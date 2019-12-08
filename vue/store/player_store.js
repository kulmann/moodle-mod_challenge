import _ from 'lodash';
import {MODE_INTRO, MODE_LEVELS, MODE_QUESTION, VALID_MODES} from '../constants';
import mixins from '../mixins';
import {ajax} from "./index";

export default {
    namespaced: true,
    state: {
        initialized: false,
        levels: null,
        levelCategories: null,
        gameSession: null,
        question: null,
        gameMode: MODE_INTRO,
        scores: {
            day: [],
            week: [],
            month: [],
            all: [],
        },
        mdl_question: null,
        mdl_answers: [],
        mdl_categories: null,
    },
    mutations: {
        setPlayerInitialized(state, initialized) {
            state.initialized = initialized;
        },
        setLevels(state, levels) {
            state.levels = levels;
        },
        setLevelCategories(state, levelCategories) {
            state.levelCategories = levelCategories;
        },
        setGameSession(state, gameSession) {
            state.gameSession = gameSession;
        },
        setQuestion(state, question) {
            state.question = question;
        },
        setMdlQuestion(state, mdl_question) {
            state.mdl_question = mdl_question;
        },
        setMdlAnswers(state, mdl_answers) {
            state.mdl_answers = mdl_answers;
        },
        setMdlCategories(state, mdl_categories) {
            state.mdl_categories = mdl_categories;
        },
        setGameMode(state, gameMode) {
            if (_.includes(VALID_MODES, gameMode)) {
                state.gameMode = gameMode;
            } else {
                console.error("omitted invalid game mode " + gameMode + ".");
            }
        },
        markLevelAsSeen(state, levelId) {
            let level = _.find(state.levels, function (level) {
                return level.id === levelId;
            });
            if (level) {
                level.seen = true;
            }
        },
        setScores(state, payload) {
            state.scores[payload.span] = payload.scores;
        },
    },
    actions: {
        /**
         * Initializes everything (gameSession, gameMode, current question, etc).
         *
         * @param context
         */
        async initPlayer(context) {
            context.dispatch('fetchGameSession').then(() => {
                let unfinishedLevel = mixins.methods.findUnfinishedLevel(context.state.levels);
                if (unfinishedLevel !== null) {
                    // the page was reloaded with an unfinished level. Load that exact level again.
                    context.dispatch('showQuestionForLevel', unfinishedLevel.id);
                } else {
                    // there is no unfinished level. Show the level overview.
                    context.commit('setGameMode', MODE_LEVELS);
                }
            }).then(() => {
                context.commit('setPlayerInitialized', true);
            });
        },
        /**
         * Fetches all assigned categories for a certain level.
         *
         * @param context
         * @param payload
         *
         * @returns {Promise<void>}
         */
        async fetchLevelCategories(context, payload) {
            const categories = await ajax('mod_challenge_get_level_categories', payload);
            context.commit('setLevelCategories', categories);
        },
        /**
         * Fetches the current game session from the server. If none exists, a new one will be created, so this
         * always returns a valid game session.
         *
         * @param context
         *
         * @returns {Promise<void>}
         */
        async fetchGameSession(context) {
            const gameSession = await ajax('mod_challenge_get_current_gamesession');
            context.commit('setGameSession', gameSession);
            return Promise.all([
                context.dispatch('fetchLevels'),
            ]);
        },
        /**
         * Forces that a new game session gets created. Dumps all old in progress game sessions.
         *
         * @param context
         *
         * @returns {Promise<void>}
         */
        async createGameSession(context) {
            const gameSession = await ajax('mod_challenge_create_gamesession');
            context.commit('setGameSession', gameSession);
            return context.dispatch('fetchLevels').then(() => {
                context.commit('setQuestion', null);
                context.commit('setGameMode', MODE_INTRO);
            });
        },
        /**
         * Closes the current game session (i.e. sets state to DUMPED). This is needed when the user goes to the admin
         * page, to reload the levels without gamesession association.
         *
         * @param context
         *
         * @returns {Promise<void>}
         */
        async cancelGameSession(context) {
            if (this.state.gameSession) {
                let args = {
                    'gamesessionid': this.state.gameSession.id
                };
                await ajax('mod_challenge_cancel_gamesession', args);
                context.commit('setGameSession', null);
                return context.dispatch('fetchLevels');
            }
        },
        /**
         * Loads the question for the given level index. Doesn't matter if it's already answered.
         *
         * @param context
         * @param levelId
         *
         * @returns {Promise<void>}
         */
        async showQuestionForLevel(context, levelId) {
            return context.dispatch('fetchQuestion', levelId).then(() => {
                context.commit('markLevelAsSeen', levelId);
                context.commit('setGameMode', MODE_QUESTION);
            });
        },
        /**
         * Submit an answer to the currently loaded question.
         *
         * @param context
         * @param payload
         *
         * @returns {Promise<void>}
         */
        async submitAnswer(context, payload) {
            const result = await ajax('mod_challenge_submit_answer', payload);
            context.commit('setQuestion', result);
            return context.dispatch('fetchGameSession');
        },
        /**
         * Submit that the time ran out on the currently loaded question.
         *
         * @param context
         *
         * @returns {Promise<*>}
         */
        async cancelAnswer(context, payload) {
            const result = await ajax('mod_challenge_cancel_answer', payload);
            context.commit('setQuestion', result);
            return context.dispatch('fetchGameSession');
        },
        /**
         * Fetches scores according to the current scoring mode of the game.
         *
         * @param context
         * @param payload
         *
         * @returns {Promise<void>}
         */
        async fetchScores(context, payload) {
            const scores = await ajax('mod_challenge_get_scores_global', payload);
            context.commit('setScores', {span: payload.span, scores: scores});
        },
        /**
         * Changes the position (+1 or -1) of the level and does the opposite to the other level.
         *
         * @param context
         * @param payload
         *
         * @returns {Promise<void>}
         */
        async changeLevelPosition(context, payload) {
            const result = await ajax('mod_challenge_set_level_position', payload);
            if (result.result === true) {
                context.dispatch('fetchLevels');
            }
        },
        /**
         * Deletes this level.
         *
         * @param context
         * @param payload
         *
         * @returns {Promise<void>}
         */
        async deleteLevel(context, payload) {
            const result = await ajax('mod_challenge_delete_level', payload);
            if (result.result === true) {
                context.dispatch('fetchLevels');
            }
        },
        /**
         * Updates the data of this level, including its categories.
         *
         * @param context
         * @param payload
         *
         * @returns {Promise<void>}
         */
        async saveLevel(context, payload) {
            const result = await ajax('mod_challenge_save_level', payload);
            context.dispatch('fetchLevels');
            return result.result;
        },

        // INTERNAL FUNCTIONS. these shouldn't be called from outside the store.
        /**
         * Fetches levels, including information on whether or not a level is finished.
         * Should not be called directly. Will be called automatically in fetchGameSession.
         *
         * @param context
         *
         * @returns {Promise<void>}
         */
        async fetchLevels(context) {
            let args = {};
            if (this.state.gameSession) {
                args['gamesessionid'] = this.state.gameSession.id;
            }
            const levels = await ajax('mod_challenge_get_levels', args);
            context.commit('setLevels', levels);
        },
        /**
         * Fetches the question, moodle question and moodle answers for the given level index.
         *
         * @param context
         * @param levelId
         *
         * @returns {Promise<void>}
         */
        async fetchQuestion(context, levelId) {
            let args = {
                gamesessionid: this.state.gameSession.id,
                levelid: levelId
            };
            const question = await ajax('mod_challenge_get_question', args);
            if (question.id === 0) {
                context.commit('setQuestion', null);
                context.commit('setMdlQuestion', null);
                context.commit('setMdlAnswers', []);
            } else {
                context.commit('setQuestion', question);
                context.dispatch('fetchMdlQuestion');
                context.dispatch('fetchMdlAnswers');
            }
        },
        /**
         * Fetches the moodle question for the currently loaded question.
         *
         * @param context
         *
         * @returns {Promise<void>}
         */
        async fetchMdlQuestion(context) {
            if (this.state.question) {
                let args = {
                    questionid: this.state.question.id
                };
                const question = await ajax('mod_challenge_get_mdl_question', args);
                context.commit('setMdlQuestion', question);
            } else {
                context.commit('setMdlQuestion', null);
            }
        },
        /**
         * Fetches the moodle answers for the currently loaded question.
         *
         * @param context
         *
         * @returns {Promise<void>}
         */
        async fetchMdlAnswers(context) {
            if (this.state.question) {
                let args = {
                    questionid: this.state.question.id
                };
                const answers = await ajax('mod_challenge_get_mdl_answers', args);
                let sortedAnswers = _.sortBy(answers, function (answer) {
                    return answer.label;
                });
                context.commit('setMdlAnswers', sortedAnswers);
            } else {
                context.commit('setMdlAnswers', []);
            }
        },
        /**
         * Fetches all moodle question categories which are applicable for this game.
         *
         * @param context
         *
         * @returns {Promise<void>}
         */
        async fetchMdlCategories(context) {
            const categories = await ajax('mod_challenge_get_mdl_categories');
            context.commit('setMdlCategories', categories);
        }
    }
}

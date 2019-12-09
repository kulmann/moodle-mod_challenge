import _ from 'lodash';
import {ajax} from "./index";

export default {
    namespaced: true,
    state: {
        initialized: false,
        finishedTournaments: [],
        activeTournaments: [],
        levels: null,
        question: null,
        mdl_question: null,
        mdl_answers: [],
    },
    mutations: {
        setPlayerInitialized(state, initialized) {
            state.initialized = initialized;
        },
        setTournaments(state, payload) {
            switch (payload.state) {
                case 'finished':
                    state.finishedTournaments = payload.tournaments;
                    break;
                case 'progress':
                    state.activeTournaments = payload.tournaments;
                    break;
                default: // change nothing
            }
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
    },
    actions: {
        /**
         * Initializes everything (gameSession, gameMode, current question, etc).
         *
         * @param context
         */
        async initPlayer(context) {
            context.dispatch('fetchUserTournaments').then(() => {
                context.commit('setPlayerInitialized', true);
            });
        },
        /**
         * Fetches the current game session from the server. If none exists, a new one will be created, so this
         * always returns a valid game session.
         *
         * @param context
         *
         * @returns {Promise<void>}
         */
        async fetchUserTournaments(context) {
            const userTournaments = await ajax('mod_challenge_get_user_tournaments');
            _.forEach(['finished', 'progress'], state => {
                context.commit('setTournaments', {
                    tournaments: _.filter(userTournaments, t => t.state === state),
                    state,
                });
            });
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
            return context.dispatch('fetchQuestion', levelId);
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
         * @param payload
         *
         * @returns {Promise<*>}
         */
        async cancelAnswer(context, payload) {
            const result = await ajax('mod_challenge_cancel_answer', payload);
            context.commit('setQuestion', result);
            return context.dispatch('fetchGameSession');
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
    }
}

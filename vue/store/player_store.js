import _ from 'lodash';
import {ajax} from "./index";

export default {
    namespaced: true,
    state: {
        initialized: false,
        finishedTournaments: [],
        activeTournaments: [],
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
    },
    getters: {
        getTournamentById: state => id => {
            const active = _.filter(state.activeTournaments, t => t.id === id);
            if (active.length > 0) {
                return _.first(active);
            }
            const finished = _.filter(state.finishedTournaments, t => t.id === id);
            if (finished.length > 0) {
                return _.first(finished);
            }
            return null;
        }
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
         * Fetches the matches of a tournament.
         *
         * @param context
         * @param payload
         * @returns {Promise<void>}
         */
        async fetchMatches(context, payload) {
            return await ajax('mod_challenge_get_user_tournament_matches', payload);
        },
        /**
         * Fetches all questions.
         *
         * @param context
         * @param payload
         * @returns {Promise<void>}
         */
        async fetchQuestions(context, payload) {
            return await ajax('mod_challenge_tournament_get_questions', payload);
        },
        /**
         * Fetches the topics of a tournament.
         *
         * @param context
         * @param payload
         * @returns {Promise<*>}
         */
        async fetchTopics(context, payload) {
            return await ajax('mod_challenge_tournament_get_topics', payload);
        },
        /**
         * Requests a question by topic id and fetches the chosen question.
         *
         * @param context
         * @param payload
         * @returns {Promise<*>}
         */
        async requestQuestionByTopic(context, payload) {
            return await ajax('mod_challenge_tournament_request_question', payload);
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
            return await ajax('mod_challenge_question_submit_answer', payload);
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
            return await ajax('mod_challenge_question_cancel_answer', payload);
        },
        /**
         * Fetches the moodle question for the currently loaded question.
         *
         * @param context
         *
         * @param payload
         * @returns {Promise<void>}
         */
        async fetchMdlQuestion(context, payload) {
            return await ajax('mod_challenge_get_mdl_question', payload);
        },
        /**
         * Fetches the moodle answers for the currently loaded question.
         *
         * @param context
         *
         * @param payload
         * @returns {Promise<void>}
         */
        async fetchMdlAnswers(context, payload) {
            const answers = await ajax('mod_challenge_get_mdl_answers', payload);
            return _.sortBy(answers, function (answer) {
                return answer.label;
            });
        },
    }
}

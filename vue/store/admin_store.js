import {ajax} from "./index";

export default {
    namespaced: true,
    state: {
        initialized: false,
        levels: [],
        levelCategories: [],
        finishedTournaments: [],
        activeTournaments: [],
        editableTournaments: [],
        mdl_categories: null,
    },
    mutations: {
        setAdminInitialized(state, initialized) {
            state.initialized = initialized;
        },
        setLevels(state, levels) {
            state.levels = levels;
        },
        setLevelCategories(state, levelCategories) {
            state.levelCategories = levelCategories;
        },
        setTournaments(state, payload) {
            switch (payload.state) {
                case 'finished':
                    state.finishedTournaments = payload.tournaments;
                    break;
                case 'progress':
                    state.activeTournaments = payload.tournaments;
                    break;
                case 'unpublished':
                    state.editableTournaments = payload.tournaments;
                    break;
                default: // change nothing
            }
        },
        setMdlCategories(state, mdl_categories) {
            state.mdl_categories = mdl_categories;
        },
    },
    actions: {
        /**
         * Initializes everything (levels, tournaments, etc).
         *
         * @param context
         */
        async initAdmin(context) {
            return Promise.all([
                context.dispatch('fetchLevels'),
                context.dispatch('fetchTournaments'),
            ]).then(() => {
                context.commit('setAdminInitialized', true);
            });
        },
        /**
         * Fetches levels, including information on whether or not a level is finished.
         * Should not be called directly. Will be called automatically in fetchGameSession.
         *
         * @param context
         * @returns {Promise<void>}
         */
        async fetchLevels(context) {
            const levels = await ajax('mod_challenge_get_levels', {});
            context.commit('setLevels', levels);
        },
        /**
         * Changes the position (+1 or -1) of the level and does the opposite to the other level.
         *
         * @param context
         * @param payload
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
         * @returns {Promise<void>}
         */
        async saveLevel(context, payload) {
            const result = await ajax('mod_challenge_save_level', payload);
            context.dispatch('fetchLevels');
            return result.result;
        },
        /**
         * Fetches all assigned categories for a certain level.
         *
         * @param context
         * @param payload
         * @returns {Promise<void>}
         */
        async fetchLevelCategories(context, payload) {
            const categories = await ajax('mod_challenge_get_level_categories', payload);
            context.commit('setLevelCategories', categories);
        },
        /**
         * Fetches all tournaments and sorts them into the three different tournament categories.
         *
         * @param context
         * @returns {Promise<void>}
         */
        async fetchTournaments(context) {
            const tournaments = await ajax('mod_challenge_get_tournaments');
            _.forEach(['finished', 'progress', 'unpublished'], state => {
                context.commit('setTournaments', {
                    tournaments: _.filter(tournaments, t => t.state === state),
                    state,
                });
            });
        },
        /**
         * Sets a state for the given tournament.
         *
         * @param context
         * @param payload
         * @returns {Promise<void>}
         */
        async setTournamentState(context, payload) {
            const result = await ajax('mod_challenge_set_tournament_state', payload);
            if (result.result === true) {
                context.dispatch('fetchTournaments');
            }
        },
        /**
         * Updates the data of the given tournament.
         *
         * @param context
         * @param payload
         * @returns {Promise<*>}
         */
        async saveTournament(context, payload) {
            const result = await ajax('mod_challenge_save_tournament', payload);
            context.dispatch('fetchTournaments');
            return result.result;
        },
        /**
         * Fetches the matches of a tournament.
         *
         * @param context
         * @param payload
         * @returns {Promise<void>}
         */
        async fetchMatches(context, payload) {
            return await ajax('mod_challenge_get_admin_tournament_matches', payload);
        },
        /**
         * Updates the participant matches of the given tournament.
         *
         * @param context
         * @param payload
         * @returns {Promise<*>}
         */
        async saveMatches(context, payload) {
            const result = await ajax('mod_challenge_save_tournament_matches', payload);
            context.dispatch('fetchTournaments');
            return result.result;
        },
        /**
         * Fetches the topics of a tournament.
         *
         * @param context
         * @param payload
         * @returns {Promise<*>}
         */
        async fetchTopics(context, payload) {
            return await ajax('mod_challenge_get_tournament_topics', payload);
        },
        /**
         * Updates the topics of the given tournament.
         *
         * @param context
         * @param payload
         * @returns {Promise<*>}
         */
        async saveTopics(context, payload) {
            const result = await ajax('mod_challenge_save_tournament_topics', payload);
            context.dispatch('fetchTournaments');
            return result.result;
        },
        /**
         * Fetches all moodle question categories which are applicable for this game.
         *
         * @param context
         * @returns {Promise<void>}
         */
        async fetchMdlCategories(context) {
            const categories = await ajax('mod_challenge_get_mdl_categories');
            context.commit('setMdlCategories', categories);
        }
    }
}

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
            switch(payload.state) {
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
        }
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
         *
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
        }
    }
}

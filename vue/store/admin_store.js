import {ajax} from "./index";

export default {
    namespaced: true,
    state: {
        initialized: false,
        categories: [],
        mdlCategories: [],
    },
    mutations: {
        setAdminInitialized(state, initialized) {
            state.initialized = initialized;
        },
        setCategories(state, categories) {
            state.categories = categories;
        },
        setMdlCategories(state, mdl_categories) {
            state.mdlCategories = mdl_categories;
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
                context.dispatch('fetchCategories'),
                context.dispatch('fetchMdlCategories')
            ]).then(() => {
                context.commit('setAdminInitialized', true);
            });
        },
        /**
         * Fetches all categories for this game.
         *
         * @param context
         * @param payload
         * @returns {Promise<void>}
         */
        async fetchCategories(context, payload) {
            const categories = await ajax('mod_challenge_admin_get_categories', payload);
            context.commit('setCategories', categories);
        },
        /**
         * Fetches all moodle question categories which are applicable for this game.
         *
         * @param context
         * @returns {Promise<void>}
         */
        async fetchMdlCategories(context) {
            const categories = await ajax('mod_challenge_admin_get_mdl_categories');
            context.commit('setMdlCategories', categories);
        },
        /**
         * Updates the data of a round, including category additions/deletions.
         *
         * @param context
         * @param payload
         * @returns {Promise<boolean>}
         */
        async saveRound(context, payload) {
            const result = await ajax('mod_challenge_admin_save_round', payload);
            await context.dispatch('fetchRounds', null, {root: true});// fetch in main store (root)
            await context.dispatch('fetchCategories');
            return result.result;
        },
        /**
         * Deletes the given round.
         *
         * @param context
         * @param payload
         * @returns {Promise<boolean>}
         */
        async deleteRound(context, payload) {
            const result = await ajax('mod_challenge_admin_delete_round', payload);
            await context.dispatch('fetchRounds', null, {root: true});// fetch in main store (root)
            return result.result;
        },
        /**
         * Schedule the given round.
         *
         * @param context
         * @param payload
         * @returns {Promise<*>}
         */
        async scheduleRound(context, payload) {
            const result = await ajax('mod_challenge_admin_schedule_round', payload);
            await context.dispatch('fetchRounds', null, {root: true});// fetch in main store (root)
            return result.result;
        },
        /**
         * Stops the given round.
         *
         * @param context
         * @param payload
         * @returns {Promise<*>}
         */
        async stopRound(context, payload) {
            const result = await ajax('mod_challenge_admin_stop_round', payload);
            await context.dispatch('fetchRounds', null, {root: true});// fetch in main store (root)
            return result.result;
        },
        /**
         * Fetches the matches of the given round.
         *
         * @param context
         * @param payload
         * @returns {Promise<void>}
         */
        async fetchRoundMatches(context, payload) {
            return await ajax('mod_challenge_admin_get_round_matches', payload);
        },
        /**
         * Fetches the questions of the given round.
         *
         * @param context
         * @param payload
         * @returns {Promise<*>}
         */
        async fetchRoundQuestions(context, payload) {
            return await ajax('mod_challenge_admin_get_round_questions', payload);
        },
        /**
         * Fetches the attempts of the given round.
         *
         * @param context
         * @param payload
         * @returns {Promise<*>}
         */
        async fetchRoundAttempts(context, payload) {
            return await ajax('mod_challenge_admin_get_round_attempts', payload);
        },
        /**
         * Updates the matches of the given round.
         *
         * @param context
         * @param payload
         * @returns {Promise<*>}
         */
        async saveMatches(context, payload) {
            const result = await ajax('mod_challenge_admin_save_matches', payload);
            // TODO: anything to re-fetch after saving matches?
            return result.result;
        },
    }
}

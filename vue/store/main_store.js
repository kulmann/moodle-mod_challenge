import moodleAjax from 'core/ajax';
import moodleStorage from 'core/localstorage';
import _ from 'lodash';
import $ from 'jquery';
import {ajax} from "./index";

export default {
    state: {
        initialized: false,
        now: new Date,
        lang: null,
        courseModuleID: 0,
        contextID: 0,
        strings: {},
        game: null,
        mdlUsers: [],
    },
    mutations: {
        updateTime(state) {
            state.now = new Date;
        },
        setInitialized(state, initialized) {
            state.initialized = initialized;
        },
        setLang(state, lang) {
            state.lang = lang;
        },
        setCourseModuleID(state, id) {
            state.courseModuleID = id;
        },
        setContextID(state, id) {
            state.contextID = id;
        },
        setStrings(state, strings) {
            state.strings = strings;
        },
        setGame(state, game) {
            state.game = game;
        },
        setMdlUsers(state, mdlUsers) {
            state.mdlUsers = mdlUsers;
        },
    },
    getters: {
        isAdminUser: state => {
            return !!state.game.mdl_user_teacher;
        },
        isInitialized: (state, getters) => {
            if (!state.initialized) {
                return false;
            }
            if (getters.isAdminUser) {
                return state.admin && state.admin.initialized;
            } else {
                return state.player && state.player.initialized;
            }
        }
    },
    actions: {
        /**
         * Initializes everything (load language, strings, game).
         *
         * @param context
         */
        async init(context) {
            return Promise.all([
                context.dispatch('startTimeTracking'),
                context.dispatch('loadLang').then(() => {
                    return Promise.all([
                        context.dispatch('loadComponentStrings'),
                        context.dispatch('fetchGame'),
                        context.dispatch('fetchMdlUsers'),
                    ]).then(() => {
                        context.commit('setInitialized', true);
                    });
                })
            ]);
        },
        /**
         * We need a reactive current time.
         *
         * @param context
         */
        async startTimeTracking(context) {
            setInterval(() => {
                context.commit('updateTime')
            }, 1000)
        },
        /**
         * Determines the current language.
         *
         * @param context
         *
         * @returns {Promise<void>}
         */
        async loadLang(context) {
            const lang = $('html').attr('lang').replace(/-/g, '_');
            context.commit('setLang', lang);
        },
        /**
         * Fetches the i18n data for the current language.
         *
         * @param context
         * @returns {Promise<void>}
         */
        async loadComponentStrings(context) {
            let lang = this.state.lang;
            const cacheKey = 'mod_challenge/strings/' + lang;
            const cachedStrings = moodleStorage.get(cacheKey);
            if (cachedStrings) {
                context.commit('setStrings', JSON.parse(cachedStrings));
            } else {
                const request = {
                    methodname: 'core_get_component_strings',
                    args: {
                        'component': 'mod_challenge',
                        lang,
                    },
                };
                const loadedStrings = await moodleAjax.call([request])[0];
                let strings = {};
                loadedStrings.forEach((s) => {
                    strings[s.stringid] = s.string;
                });
                context.commit('setStrings', strings);
                moodleStorage.set(cacheKey, JSON.stringify(strings));
            }
        },
        /**
         * Fetches game options and active user info.
         *
         * @param context
         *
         * @returns {Promise<void>}
         */
        async fetchGame(context) {
            const game = await ajax('mod_challenge_get_game');
            context.commit('setGame', game);
        },
        /**
         * Fetches all non-teacher moodle users that have access to this course.
         *
         * @param context
         *
         * @returns {Promise<void>}
         */
        async fetchMdlUsers(context) {
            const mdlUsers = await ajax('mod_challenge_get_mdl_users');
            context.commit('setMdlUsers', mdlUsers);
        }
    }
};



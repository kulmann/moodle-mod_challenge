import Vue from 'vue';
import Vuikit from 'vuikit';
import '@vuikit/theme';
import 'vue-awesome/icons';
import Icon from 'vue-awesome/components/Icon';
import VueRouter from 'vue-router';
import {store} from './store/index';
import notFound from './components/not-found';
import loadingScreen from './components/loading-screen';
import adminScreen from './components/admin/admin-screen';
import gameScreen from './components/game/game-screen';
import adminStore from "./store/admin_store";
import playerStore from "./store/player_store";
import constants from "./constants";

function init(coursemoduleid, contextid) {
    // We need to overwrite the variable for lazy loading.
    __webpack_public_path__ = M.cfg.wwwroot + '/mod/challenge/amd/build/';

    Vue.use(Vuikit);
    Vue.use(VueRouter);
    Vue.component('v-icon', Icon);

    require('./styles/theme.scss');

    store.commit('setCourseModuleID', coursemoduleid);
    store.commit('setContextID', contextid);
    store.dispatch('init').then(() => {
        if (store.getters.isAdminUser) {
            store.registerModule('admin', adminStore);
            return store.dispatch('admin/initAdmin');
        } else {
            store.registerModule('player', playerStore);
            return store.dispatch('player/initPlayer');
        }
    });

    const routes = [
        {
            path: '/',
            redirect: {name: 'loading-screen'}
        }, {
            path: '/loading',
            component: loadingScreen,
            name: 'loading-screen',
            meta: {title: 'loading_screen_title'}
        }, {
            path: '/game',
            component: gameScreen,
            name: 'game-screen',
            meta: {title: 'game_screen_title'},
            children: [{
                path: 'tournaments',
                name: 'player-tournament-list',
            }, {
                path: 'tournament/:tournamentId',
                name: 'player-tournament-show',
            }, {
                path: 'question/:matchId/:topicId',
                name: 'player-question-play',
            }]
        }, {
            path: '/admin',
            component: adminScreen,
            name: 'admin-screen',
            meta: {title: 'admin_screen_title'},
            children: [{
                path: '/',
                redirect: {name: constants.ROUTE_ADMIN_ROUNDS},
            }, {
                path: 'rounds',
                name: constants.ROUTE_ADMIN_ROUNDS,
            }, {
                path: 'round/:roundId?',
                name: constants.ROUTE_ADMIN_ROUND_EDIT,
            }]
        }, {
            path: '*',
            component: notFound,
            meta: {title: 'route_not_found'}
        },
    ];

    // base URL is /mod/challenge/view.php/[course module id]/
    const currentUrl = window.location.pathname;
    const base = currentUrl.substr(0, currentUrl.indexOf('.php')) + '.php/' + coursemoduleid + '/';

    const router = new VueRouter({
        mode: 'history',
        routes,
        base
    });

    router.beforeEach((to, from, next) => {
        // Find a translation for the title.
        if (to.hasOwnProperty('meta') && to.meta.hasOwnProperty('title')) {
            if (store.state.strings.hasOwnProperty(to.meta.title)) {
                document.title = store.state.strings[to.meta.title];
            }
        }
        next()
    });

    new Vue({
        el: '#mod-challenge-app',
        store,
        router,
    });
}

export {init};

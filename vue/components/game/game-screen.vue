<template lang="pug">
    #challenge-game_screen
        .uk-clearfix
        loadingAlert(v-if="!isInitialized", message="Loading Game").uk-text-center
        failureAlert(v-else-if="isAdminUser", :message="strings.game_not_allowed").uk-text-center
        template(v-else)
            tournaments(v-if="viewMode === VIEW_MODE_TOURNAMENTS")
            tournamentShow(v-else-if="viewMode === VIEW_MODE_TOURNAMENT_SHOW")
            question(v-else-if="viewMode === VIEW_MODE_QUESTION_PLAY")
</template>

<script>
    import mixins from '../../mixins';
    import {mapGetters, mapState} from 'vuex';
    import VkGrid from "vuikit/src/library/grid/components/grid";
    import loadingAlert from '../helper/loading-alert';
    import infoAlert from "../helper/info-alert";
    import tournaments from "./tournaments/tournaments";
    import tournamentShow from "./tournament/tournament-show";
    import question from "./question/question";

    export default {
        mixins: [mixins],
        data() {
            return {
                VIEW_MODE_NONE: 'none',
                VIEW_MODE_TOURNAMENTS: 'player-tournament-list',
                VIEW_MODE_TOURNAMENT_SHOW: 'player-tournament-show',
                VIEW_MODE_QUESTION_PLAY: 'player-question-play',
            }
        },
        computed: {
            ...mapState([
                'strings',
            ]),
            ...mapGetters([
                'isAdminUser',
                'isInitialized'
            ]),
            viewMode() {
                const viewModes = [
                    this.VIEW_MODE_TOURNAMENTS,
                    this.VIEW_MODE_TOURNAMENT_SHOW,
                    this.VIEW_MODE_QUESTION_PLAY,
                ];
                const viewMode = this.$route.name;
                if (viewModes.includes(viewMode)) {
                    return viewMode;
                } else {
                    return this.VIEW_MODE_NONE;
                }
            },
        },
        mounted() {
            if (this.viewMode === this.VIEW_MODE_NONE) {
                this.$router.push({name: this.VIEW_MODE_TOURNAMENTS});
            }
        },
        components: {
            question,
            tournaments,
            tournamentShow,
            infoAlert,
            loadingAlert,
            VkGrid
        }
    }
</script>

<template lang="pug">
    #challenge-game_screen
        .uk-clearfix
        loadingAlert(v-if="!isInitialized", message="Loading Game").uk-text-center
        failureAlert(v-else-if="isAdminUser", :message="strings.game_not_allowed").uk-text-center
        infoAlert(v-else-if="isNotStarted", :message="strings.game_not_started").uk-text-center
        template(v-else)
            matches(v-if="viewMode === VIEW_MODE_MATCH_SHOW", :rounds="rounds", :matches="matches")
            question(v-else-if="viewMode === VIEW_MODE_QUESTION_PLAY")
</template>

<script>
    import langMixins from '../../mixins/lang-mixins';
    import {mapGetters, mapState} from 'vuex';
    import VkGrid from "vuikit/src/library/grid/components/grid";
    import loadingAlert from '../helper/loading-alert';
    import infoAlert from "../helper/info-alert";
    import matches from "./matches/matches";
    import question from "./question/question";

    export default {
        mixins: [langMixins],
        data() {
            return {
                VIEW_MODE_NONE: 'none',
                VIEW_MODE_MATCH_SHOW: 'player-match-show',
                VIEW_MODE_QUESTION_PLAY: 'player-question-play',
            }
        },
        computed: {
            ...mapState([
                'strings',
                'rounds'
            ]),
            ...mapGetters([
                'isAdminUser',
                'isInitialized',
                'getCurrentRound'
            ]),
            ...mapState('player', ['matches']),
            viewMode() {
                const viewModes = [
                    this.VIEW_MODE_MATCH_SHOW,
                    this.VIEW_MODE_QUESTION_PLAY,
                ];
                const viewMode = this.$route.name;
                if (viewModes.includes(viewMode)) {
                    return viewMode;
                } else {
                    return this.VIEW_MODE_NONE;
                }
            },
            isNotStarted() {
                return this.matches.length === 0;
            }
        },
        mounted() {
            if (this.viewMode === this.VIEW_MODE_NONE) {
                this.$router.push({name: this.VIEW_MODE_MATCH_SHOW});
            }
        },
        components: {
            question,
            matches,
            infoAlert,
            loadingAlert,
            VkGrid
        }
    }
</script>

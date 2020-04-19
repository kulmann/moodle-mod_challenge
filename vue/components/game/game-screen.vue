<template lang="pug">
    #challenge-game_screen
        .uk-clearfix
        loadingAlert(v-if="!isInitialized", message="Loading Game").uk-text-center
        failureAlert(v-else-if="isAdminUser", :message="strings.game_not_allowed").uk-text-center
        template(v-else)
            matches(v-if="viewMode === VIEW_MODE_MATCHES_LIST", :rounds="rounds", :matches="matches")
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
                VIEW_MODE_MATCHES_LIST: 'player-matches-list',
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
            ...mapState('player', [
                'matches'
            ]),
            viewMode() {
                const viewModes = [
                    this.VIEW_MODE_MATCHES_LIST,
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
                this.$router.push({name: this.VIEW_MODE_MATCHES_LIST});
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

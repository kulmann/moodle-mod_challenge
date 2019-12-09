<template lang="pug">
    #challenge-game_screen
        .uk-clearfix
        loadingAlert(v-if="!isInitialized", message="Loading Game").uk-text-center
        failureAlert(v-else-if="isAdminUser", :message="strings.game_not_allowed").uk-text-center
        template(v-else)
            .uk-margin-small-top.uk-margin-small-bottom
                h3 {{ strings.game_tournaments_active_title }}
                infoAlert(v-if="activeTournaments.length === 0", :message="strings.game_tournaments_active_none")
                tournamentList(v-else, :tournaments="activeTournaments")
                h3 {{ strings.game_tournaments_finished_title }}
                infoAlert(v-if="finishedTournaments.length === 0", :message="strings.game_tournaments_finished_none")
                tournamentList(v-else, :tournaments="finishedTournaments")
</template>

<script>
    import mixins from '../../mixins';
    import {mapGetters, mapState} from 'vuex';
    import loadingAlert from '../helper/loading-alert';
    import VkGrid from "vuikit/src/library/grid/components/grid";
    import tournamentList from "./tournament-list";
    import InfoAlert from "../helper/info-alert";

    export default {
        mixins: [mixins],
        computed: {
            ...mapState([
                'strings',
            ]),
            ...mapGetters([
                'isAdminUser',
                'isInitialized'
            ]),
            ...mapState('player', [
                'activeTournaments',
                'finishedTournaments',
            ])
        },
        components: {
            InfoAlert,
            tournamentList,
            loadingAlert,
            VkGrid
        }
    }
</script>

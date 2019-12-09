<template lang="pug">
    div
        .uk-card.uk-card-default
            .uk-card-header
                h3 {{ strings.game_tournaments_active_title }}
            .uk-card-body
                infoAlert(v-if="activeTournaments.length === 0", :message="strings.game_tournaments_active_none")
                tournament-list(v-else, :tournaments="activeTournaments")
        .uk-card.uk-card-default.uk-margin-small-top
            .uk-card-header
                h3 {{ strings.game_tournaments_finished_title }}
            .uk-card-body
                infoAlert(v-if="finishedTournaments.length === 0", :message="strings.game_tournaments_finished_none")
                tournamentList(v-else, :tournaments="finishedTournaments")
</template>

<script>
    import {mapState} from 'vuex';
    import tournamentList from "./tournament-list";
    import infoAlert from "../../helper/info-alert";

    export default {
        computed: {
            ...mapState([
                'strings',
            ]),
            ...mapState('player', [
                'activeTournaments',
                'finishedTournaments',
            ])
        },
        components: {
            infoAlert,
            tournamentList
        }
    }
</script>

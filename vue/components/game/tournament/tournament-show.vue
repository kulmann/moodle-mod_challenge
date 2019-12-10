<template lang="pug">
    .uk-card.uk-card-default
        .uk-card-header
            h3 {{ tournament.name }}
        .uk-card-body
            span game content area
</template>

<script>
    import {mapState, mapGetters} from 'vuex';

    export default {
        computed: {
            ...mapState([
                'strings'
            ]),
            ...mapGetters({
                getTournamentById: 'player/getTournamentById',
            }),
            tournamentId () {
                return parseInt(this.$route.params.tournamentId);
            },
            tournament () {
                return this.getTournamentById(this.tournamentId);
            },
        }
    }
</script>

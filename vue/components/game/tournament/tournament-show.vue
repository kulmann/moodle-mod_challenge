<template lang="pug">
    .uk-card.uk-card-default
        .uk-card-header
            h3 {{ tournament.name }}
        .uk-card-body
            p show most recent match (if active: let user play)
            p let user navigate to older matches to see how he performed
            p each in the same format, either match-done or match-open.
</template>

<script>
    import {mapActions, mapGetters, mapState} from 'vuex';

    export default {
        data () {
            return {
                matches: [],
            }
        },
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
        },
        methods: {
            ...mapActions({
                fetchMatches: 'player/fetchMatches',
            }),
            loadMatches() {
                this.matches = this.fetchMatches({
                    tournamentid: this.tournamentId
                });
            }
        },
        mounted() {
            this.loadMatches();
        },
        watch: {
            tournamentId () {
                this.loadMatches();
            }
        }
    }
</script>

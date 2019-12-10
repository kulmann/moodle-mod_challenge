<template lang="pug">
    .uk-card.uk-card-default
        .uk-card-header
            h3 {{ tournament.name }}
        .uk-card-body
            .uk-flex.uk-flex-middle.uk-flex-center.uk-text-center.uk-margin-small-bottom
                button.btn.btn-default(:disabled="isFirstMatch", @click="goToPrevMatch")
                    v-icon(name="chevron-left")
                div
                    b.uk-margin-small-left.uk-margin-small-right {{ strings.game_tournament_match_step | stringParams(matchIndex + 1) }}
                    template(v-if="match")
                        br
                        i(v-if="match.open") {{ strings.game_tournament_match_lbl_open }}
                        span.uk-text-success(v-else-if="match.mdl_user_winner === ownUserId") {{ strings.game_tournament_match_lbl_won }}
                        span.uk-text-danger(v-else) {{ strings.game_tournament_match_lbl_lost }}
                button.btn.btn-default(:disabled="isLastMatch", @click="goToNextMatch")
                    v-icon(name="chevron-right")
            failureAlert(v-if="match === null", :message="strings.game_tournament_match_show_error")
            template(v-else)
                matchHeader(:match="match").uk-margin-small-bottom
                matchOpen(v-if="match.open", :match="match")
                matchDone(v-else, :match="match")
            p show most recent match (if active: let user play)
            p let user navigate to older matches to see how he performed
            p each in the same format, either match-done or match-open.
</template>

<script>
    import Mixins from '../../../mixins';
    import {mapActions, mapGetters, mapState} from 'vuex';
    import failureAlert from "../../helper/failure-alert";
    import matchHeader from "./match/match-header";
    import matchOpen from "./match/match-open";
    import matchDone from "./match/match-done";

    export default {
        mixins: [Mixins],
        data() {
            return {
                matches: [],
                matchIndex: 0,
            }
        },
        computed: {
            ...mapState([
                'strings',
                'game',
            ]),
            ...mapGetters({
                getTournamentById: 'player/getTournamentById',
            }),
            tournamentId() {
                return parseInt(this.$route.params.tournamentId);
            },
            tournament() {
                return this.getTournamentById(this.tournamentId);
            },
            match() {
                if (this.matchIndex >= 0 && this.matchIndex < this.matches.length) {
                    return this.matches[this.matchIndex];
                }
                return null;
            },
            isFirstMatch() {
                return this.matchIndex === 0;
            },
            isLastMatch() {
                return this.matchIndex === this.matches.length - 1;
            },
            ownUserId() {
                return this.game.mdl_user;
            }
        },
        methods: {
            ...mapActions({
                fetchMatches: 'player/fetchMatches',
            }),
            loadMatches() {
                this.fetchMatches({
                    tournamentid: this.tournamentId
                }).then(matches => {
                    this.matches = _.sortBy(matches, 'step');
                    this.matchIndex = this.matches.length - 1;
                });
            },
            goToPrevMatch() {
                if (this.matchIndex > 0) {
                    this.matchIndex--;
                }
            },
            goToNextMatch() {
                if (this.matchIndex < this.matches.length - 1) {
                    this.matchIndex++;
                }
            }
        },
        mounted() {
            this.loadMatches();
        },
        watch: {
            tournamentId() {
                this.loadMatches();
            }
        },
        components: {
            matchHeader,
            matchDone,
            matchOpen,
            failureAlert
        }
    }
</script>

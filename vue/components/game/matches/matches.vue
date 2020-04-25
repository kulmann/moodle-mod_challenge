<template lang="pug">
    .uk-card.uk-card-default.uk-card-body
        loading-alert(v-if="loading", :message="strings.game_match_loading")
        template(v-else)
            .uk-flex.uk-flex-middle.uk-flex-center.uk-text-center.uk-margin-small-bottom
                button.btn.btn-default(:disabled="isFirstMatch", @click="goToPrevMatch")
                    v-icon(name="chevron-left")
                div
                    b.uk-margin-small-left.uk-margin-small-right {{ strings.game_match_step | stringParams({step: matchIndex + 1, total: matches.length}) }}
                    template(v-if="match")
                        br
                        i(v-if="match.open") {{ strings.game_match_lbl_open }}
                        b.uk-text-success(v-else-if="match.mdl_user_winner === ownUserId") {{ strings.game_match_lbl_won }}
                        b.uk-text-danger(v-else) {{ strings.game_match_lbl_lost }}
                button.btn.btn-default(:disabled="isLastMatch", @click="goToNextMatch")
                    v-icon(name="chevron-right")
            failure-alert(v-if="match === null", :message="strings.game_match_show_error")
            match-show(v-else, :round="round", :match="match", :questions="questions", :attempts="attempts", :own-user-id="ownUserId" )
</template>

<script>
    import langMixins from '../../../mixins/lang-mixins';
    import {mapActions, mapGetters, mapState} from 'vuex';
    import isNil from 'lodash/isNil';
    import first from 'lodash/first';
    import FailureAlert from "../../helper/failure-alert";
    import LoadingAlert from "../../helper/loading-alert";
    import MatchShow from "./match-show";

    export default {
        mixins: [langMixins],
        props: {
            rounds: {
                type: Array,
                required: true
            },
            matches: {
                type: Array,
                required: true
            }
        },
        data() {
            return {
                loading: true,
                matchIndex: undefined,
                questions: [],
                attempts: [],
            }
        },
        computed: {
            ...mapState([
                'strings',
                'game',
            ]),
            ...mapGetters(['getRoundById']),
            ...mapGetters('player', [
                'getMatchById'
            ]),
            forcedMatchId() {
                if (!isNil(this.$route.params.matchId)) {
                    return parseInt(this.$route.params.matchId);
                }
                return undefined;
            },
            match() {
                if (!isNil(this.matchIndex)) {
                    if (this.matchIndex >= 0 && this.matchIndex < this.matches.length) {
                        return this.matches[this.matchIndex];
                    }
                }
                if (!isNil(this.forcedMatchId)) {
                    return this.getMatchById(this.forcedMatchId);
                }
                return first(this.matches);
            },
            round() {
                if (!isNil(this.match)) {
                    return this.getRoundById(this.match.round);
                }
                return first(this.rounds);
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
                fetchMatchQuestions: 'player/fetchMatchQuestions',
                fetchMatchAttempts: 'player/fetchMatchAttempts',
            }),
            async initData() {
                this.loading = true;
                await this.loadQuestions();
                this.matchIndex = isNil(this.match) ? undefined : this.matches.indexOf(this.match);
                this.loading = false;
            },
            async loadQuestions() {
                const matchId = this.match ? this.match.id : undefined;
                if (matchId) {
                    this.questions = await this.fetchMatchQuestions({matchid: matchId});
                    this.attempts = await this.fetchMatchAttempts({matchid: matchId});
                } else {
                    this.questions = [];
                    this.attempts = [];
                }
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
            this.initData();
        },
        watch: {
            forcedMatchId() {
                this.initData();
            }
        },
        components: {
            MatchShow,
            LoadingAlert,
            FailureAlert
        }
    }
</script>

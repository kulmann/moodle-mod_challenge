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
                matchTopics(:match="match", :topics="getTopicsByStep(match.step)", :questions="questions", :ownUserId="ownUserId")
</template>

<script>
    import Mixins from '../../../mixins';
    import {mapActions, mapGetters, mapState} from 'vuex';
    import failureAlert from "../../helper/failure-alert";
    import matchHeader from "./match/match-header";
    import matchTopics from "./match/match-topics";

    export default {
        mixins: [Mixins],
        data() {
            return {
                matches: [],
                matchIndex: 0,
                questions: [],
                topicsBySteps: {},
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
                fetchTopics: 'player/fetchTopics',
                fetchQuestions: 'player/fetchQuestions',
            }),
            initData() {
                this.loadMatches();
                this.loadTopics();
                this.loadQuestions();
            },
            loadMatches() {
                this.fetchMatches({
                    tournamentid: this.tournamentId
                }).then(matches => {
                    this.matches = _.sortBy(matches, 'step');
                    this.matchIndex = this.matches.length - 1;
                });
            },
            loadTopics() {
                this.fetchTopics({
                    tournamentid: this.tournamentId
                }).then(topics => {
                    let map = {};
                    _.forEach(topics, topic => {
                        if (!map.hasOwnProperty(topic.step)) {
                            map[topic.step] = [];
                        }
                        map[topic.step].push(topic);
                    });
                    this.topicsBySteps = map;
                });
            },
            loadQuestions() {
                this.fetchQuestions({
                    tournamentid: this.tournamentId
                }).then(questions => {
                    this.questions = questions;
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
            },
            getTopicsByStep(step) {
                if (this.topicsBySteps.hasOwnProperty(step)) {
                    return this.topicsBySteps[step];
                }
                return [];
            },
        },
        mounted() {
            this.initData();
        },
        watch: {
            tournamentId() {
                this.initData();
            }
        },
        components: {
            matchHeader,
            matchTopics,
            failureAlert
        }
    }
</script>

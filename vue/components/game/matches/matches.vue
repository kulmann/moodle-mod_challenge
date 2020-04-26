<template lang="pug">
    .uk-card.uk-card-default.uk-card-body
        loading-alert(v-if="loading", :message="strings.game_match_loading")
        template(v-else)
            match-nav(v-model="matchIndex", :own-user-id="ownUserId", :round="round", :match="match", :matches="matches")
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
    import MatchNav from "./match-nav";

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
            MatchNav,
            MatchShow,
            LoadingAlert,
            FailureAlert
        }
    }
</script>

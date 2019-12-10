<template lang="pug">
    .uk-card.uk-card-default
        .uk-card-body(v-if="matches === null")
            loadingAlert(:message="strings.admin_tournament_participants_loading")
        template(v-else)
            .uk-card-body
                h3 {{ strings.admin_tournament_title_matches }}
                ul.uk-tab.uk-margin-small-bottom
                    li(:class="{'uk-active': activeTab === TAB_USERS}")
                        a(@click="setActiveTab(TAB_USERS)") {{  strings.admin_nav_matches_users }}
                    li(:class="{'uk-active': activeTab === TAB_MATCHES}")
                        a(@click="setActiveTab(TAB_MATCHES)") {{  strings.admin_nav_matches_pairs }}
                usersSelect(v-if="activeTab === TAB_USERS", :tournament="tournament", v-model="participants", @input="clearMatches")
                matchesSelect(v-else-if="activeTab === TAB_MATCHES", :tournament="tournament", :participants="participants", v-model="matches")
            .uk-card-footer.uk-text-right
                button.btn.btn-primary(@click="save()", :disabled="saving || isDataInvalid")
                    v-icon(name="save").uk-margin-small-right
                    span {{ strings.admin_btn_save }}
                button.btn.btn-default(@click="goToTournamentList()", :disabled="saving").uk-margin-small-left
                    v-icon(name="ban").uk-margin-small-right
                    span {{ strings.admin_btn_cancel }}
                .uk-alert.uk-alert-primary.uk-text-center(uk-alert, v-if="saving")
                    p
                        span {{ strings.admin_tournament_participants_saving }}
                        loadingIcon
</template>

<script>
    import {mapActions, mapState} from 'vuex';
    import mixins from '../../../mixins';
    import loadingAlert from "../../helper/loading-alert";
    import btnAdd from '../btn-add';
    import loadingIcon from "../../helper/loading-icon";
    import usersSelect from "./users-select";
    import matchesSelect from "./matches-select";

    export default {
        mixins: [mixins],
        props: {
            tournament: Object,
        },
        data() {
            return {
                TAB_USERS: 'users',
                TAB_MATCHES: 'matches',
                activeTab: 'users',
                participants: [],
                matches: null,
                saving: false,
            }
        },
        computed: {
            ...mapState([
                'strings',
                'game',
                'mdlUsers',
            ]),
            isDataInvalid() {
                return this.matches === null || this.matches.length === 0;
            },
        },
        methods: {
            ...mapActions({
                fetchMatches: 'admin/fetchMatches',
                saveMatches: 'admin/saveMatches',
            }),
            setActiveTab(tab) {
                this.activeTab = tab;
            },
            initData(tournament) {
                this.fetchMatches({tournamentid: tournament.id}).then(matches => {
                    // collect matches
                    this.matches = _.map(matches, match => {
                        return {
                            mdl_user_1: match.mdl_user_1,
                            mdl_user_2: match.mdl_user_2,
                        };
                    });
                    // collect participants
                    let participantIds = [];
                    _.forEach(this.matches, match => {
                        participantIds.push(match.mdl_user_1);
                        participantIds.push(match.mdl_user_2);
                    });
                    this.participants = _.filter(this.mdlUsers, user => participantIds.includes(user.id));
                });
            },
            clearMatches() {
                this.matches = [];
            },
            goToTournamentList() {
                this.$router.push({name: 'admin-tournament-list'});
            },
            save() {
                if (this.isDataInvalid) {
                    return;
                }
                let payload = {
                    tournamentid: this.tournament.id,
                    matches: this.matches,
                };
                this.saving = true;
                this.saveMatches(payload)
                    .then((successful) => {
                        this.saving = false;
                        if (successful) {
                            this.goToTournamentList();
                        }
                    });
            },
        },
        mounted() {
            this.initData(this.tournament);
        },
        watch: {
            tournament(tournament) {
                this.initData(tournament);
            },
        },
        components: {
            matchesSelect,
            usersSelect,
            loadingIcon,
            loadingAlert,
            btnAdd,
        },
    }
</script>

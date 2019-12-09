<template lang="pug">
    .uk-card.uk-card-default
        .uk-card-body(v-if="pairings === null")
            loadingAlert(:message="strings.admin_tournament_participants_loading")
        template(v-else)
            .uk-card-body
                h3 {{ strings.admin_tournament_title_pairings }}
                ul.uk-tab.uk-margin-small-bottom
                    li(:class="{'uk-active': activeTab === TAB_USERS}")
                        a(@click="setActiveTab(TAB_USERS)") {{  strings.admin_nav_pairings_users }}
                    li(:class="{'uk-active': activeTab === TAB_PAIRINGS}")
                        a(@click="setActiveTab(TAB_PAIRINGS)") {{  strings.admin_nav_pairings_pairs }}
                usersSelect(v-if="activeTab === TAB_USERS", :tournament="tournament", v-model="participants")
            .uk-card-footer.uk-text-right
                button.btn.btn-primary(@click="save()", :disabled="saving")
                    v-icon(name="save").uk-margin-small-right
                    span {{ strings.admin_btn_save }}
                button.btn.btn-default(@click="goToTournamentList()", :disabled="saving").uk-margin-small-left
                    v-icon(name="ban").uk-margin-small-right
                    span {{ strings.admin_btn_cancel }}
                .uk-alert.uk-alert-primary.uk-text-center(uk-alert, v-if="saving")
                    p
                        span {{ strings.admin_tournament_msg_saving }}
                        loadingIcon

</template>

<script>
    import {mapActions, mapState} from 'vuex';
    import mixins from '../../../mixins';
    import loadingAlert from "../../helper/loading-alert";
    import btnAdd from '../btn-add';
    import loadingIcon from "../../helper/loading-icon";
    import UsersSelect from "./users-select";

    export default {
        mixins: [mixins],
        props: {
            tournament: Object,
        },
        data() {
            return {
                TAB_USERS: 'users',
                TAB_PAIRINGS: 'pairings',
                activeTab: 'users',
                participants: [],
                pairings: null,
                saving: false,
            }
        },
        computed: {
            ...mapState([
                'strings',
                'game',
                'mdlUsers',
            ]),
        },
        methods: {
            ...mapActions({
                fetchPairings: 'admin/fetchPairings',
                savePairings: 'admin/savePairings',
            }),
            setActiveTab(tab) {
                this.activeTab = tab;
            },
            initData(tournament) {
                let pairings = this.fetchPairings({tournamentid: tournament.id});
                this.pairings = _.map(pairings, pairing => {
                    return {
                        mdl_user_1: pairing.mdl_user_1,
                        mdl_user_2: pairing.mdl_user_2,
                    };
                });
                this.participants = this.mdlUsers;
            },
            goToTournamentList() {
                this.$router.push({name: 'admin-tournament-list'});
            },
            save() {
                let payload = {
                    tournamentid: this.tournament.id,
                };
                this.saving = true;
                this.savePairings(payload)
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
            tournament (tournament) {
                this.initData(tournament);
            },
        },
        components: {
            UsersSelect,
            loadingIcon,
            loadingAlert,
            btnAdd,
        },
    }
</script>

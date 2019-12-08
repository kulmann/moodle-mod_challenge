<template lang="pug">
    .uk-card.uk-card-default
        .uk-card-body(v-if="pairings === null")
            loadingAlert(:message="strings.admin_tournament_participants_loading")
        template(v-else)
            .uk-card-body
                h3 {{ strings.admin_tournament_title_pairings }}

                
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
    import mixins from '../../mixins';
    import loadingAlert from "../helper/loading-alert";
    import btnAdd from './btn-add';
    import loadingIcon from "../helper/loading-icon";

    export default {
        mixins: [mixins],
        props: {
            tournament: Object,
        },
        data() {
            return {
                pairings: null,
                saving: false,
            }
        },
        computed: {
            ...mapState([
                'strings',
                'game',
            ]),
        },
        methods: {
            ...mapActions({
                fetchPairings: 'admin/fetchPairings',
                savePairings: 'admin/savePairings',
            }),
            initData(tournament) {
                let pairings = this.fetchPairings({tournamentid: tournament.id});
                console.log(pairings);
                this.pairings = _.map(pairings, pairing => {
                    return {
                        mdl_user_1: pairing.mdl_user_1,
                        mdl_user_2: pairing.mdl_user_2,
                    };
                });
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
            tournament: function (tournament) {
                this.initData(tournament);
            },
        },
        components: {
            loadingIcon,
            loadingAlert,
            btnAdd,
        },
    }
</script>

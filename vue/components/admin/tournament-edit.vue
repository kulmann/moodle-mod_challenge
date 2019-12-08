<template lang="pug">
    .uk-card.uk-card-default
        .uk-card-body(v-if="data === null")
            loadingAlert(:message="strings.admin_tournament_loading")
        template(v-else)
            .uk-card-body
                form.uk-form-stacked(@submit.prevent="save()")
                    h3(v-if="editing") {{ strings.admin_tournament_title_edit }}
                    h3(v-else) {{ strings.admin_tournament_title_add }}

                    vk-grid(matched).uk-grid-divider
                        div.uk-width-1-1
                            .uk-margin-small
                                label.uk-form-label {{ strings.admin_tournament_lbl_name }}
                                .uk-form-controls
                                    input.uk-input(v-model="data.name", :placeholder="strings.admin_tournament_lbl_name")
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
                data: null,
                saving: false,
            }
        },
        computed: {
            ...mapState([
                'strings',
                'game',
            ]),
            editing() {
                return this.tournament !== null;
            },
        },
        methods: {
            ...mapActions({
                saveTournament: 'admin/saveTournament',
            }),
            initTournamentData(tournament) {
                if (tournament === null) {
                    this.data = {
                        id: null,
                        game: this.game.id,
                        name: '',
                    };
                } else {
                    this.data = tournament;
                }
            },
            goToTournamentList() {
                this.$router.push({name: 'admin-tournament-list'});
            },
            save() {
                let result = {
                    tournamentid: (this.data.id || 0),
                    name: this.data.name,
                };
                this.saving = true;
                this.saveTournament(result)
                    .then((successful) => {
                        this.saving = false;
                        if (successful) {
                            this.goToTournamentList();
                        }
                    });
            },
        },
        mounted() {
            this.initTournamentData(this.tournament);
        },
        watch: {
            tournament: function (tournament) {
                this.initTournamentData(tournament);
            },
        },
        components: {
            loadingIcon,
            loadingAlert,
            btnAdd,
        },
    }
</script>

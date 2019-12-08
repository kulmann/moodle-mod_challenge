<template lang="pug">
    .uk-card.uk-card-default
        .uk-card-header
            h3 {{ strings.admin_tournaments_title }}
        .uk-card-body
            h4 {{ strings.admin_tournaments_title_unpublished }}
            template(v-if="editableTournaments.length === 0")
                infoAlert(:message="strings.admin_tournaments_none_unpublished")
                btnAdd(@click="createTournament")
            template(v-else)
                p {{ strings.admin_tournaments_intro_unpublished }}
                btnAdd(@click="createTournament")
                table.uk-table.uk-table-small.uk-table-striped
                    tbody
                        template(v-for="tournament in editableTournaments")
                            tr.uk-text-nowrap(:key="tournament.id")
                                td.uk-table-auto.uk-text-left.uk-text-middle {{ tournament.name }}
                                td.actions.uk-table-shrink.uk-preserve-width
                                    button.btn.btn-default(@click="editTournament(tournament)")
                                        v-icon(name="regular/edit")
                                    button.btn.btn-default(@click="deleteTournamentAsk(level)")
                                        v-icon(name="trash")
                            tr(v-if="deleteConfirmationTournamentId === tournament.id")
                                td(colspan="3")
                                    .uk-alert.uk-alert-danger(uk-alert)
                                        vk-grid
                                            .uk-width-expand
                                                v-icon(name="exclamation-circle").uk-margin-small-right
                                                span {{ strings.admin_tournament_delete_confirm | stringParams(tournament.name) }}
                                            .uk-width-auto
                                                button.btn.btn-danger.uk-margin-small-left(@click="deleteTournamentConfirm(tournament)")
                                                    span {{ strings.admin_btn_confirm_delete }}
                btnAdd(@click="createTournament")
            h4 {{ strings.admin_tournaments_title_progress }}
            template(v-if="activeTournaments.length === 0")
                infoAlert(:message="strings.admin_tournaments_none_progress")
            template(v-else)
                p {{ strings.admin_tournaments_intro_progress }}
                table.uk-table.uk-table-small.uk-table-striped
                    tbody
                        template(v-for="tournament in activeTournaments")
                            tr.uk-text-nowrap(:key="tournament.id")
                                td.uk-table-auto.uk-text-left.uk-text-middle {{ tournament.name }}
                                td.actions.uk-table-shrink.uk-preserve-width
            h4 {{ strings.admin_tournaments_title_finished }}
            template(v-if="finishedTournaments.length === 0")
                infoAlert(:message="strings.admin_tournaments_none_finished")
            template(v-else)
                p {{ strings.admin_tournaments_intro_finished }}
                table.uk-table.uk-table-small.uk-table-striped
                    tbody
                        template(v-for="tournament in finishedTournaments")
                            tr.uk-text-nowrap(:key="tournament.id")
                                td.uk-table-auto.uk-text-left.uk-text-middle {{ tournament.name }}
                                td.actions.uk-table-shrink.uk-preserve-width
</template>

<script>
    import {mapActions, mapState} from 'vuex';
    import _ from 'lodash';
    import mixins from '../../mixins';
    import infoAlert from '../helper/info-alert';
    import btnAdd from './btn-add';
    import VkGrid from "vuikit/src/library/grid/components/grid";

    export default {
        mixins: [mixins],
        props: {
            editableTournaments: Array,
            activeTournaments: Array,
            finishedTournaments: Array,
        },
        data() {
            return {
                publishConfirmationTournamentId: null,
                deleteConfirmationTournamentId: null,
            }
        },
        computed: {
            ...mapState([
                'contextID',
                'strings',
            ]),
        },
        methods: {
            ...mapActions('admin', [
                'setTournamentState',
            ]),
            createTournament() {
                this.$router.push({name: 'admin-tournament-edit'});
            },
            editTournament(tournament) {
                this.$router.push({name: 'admin-tournament-edit', params: {tournamentId: tournament.id}});
            },
            publishTournamentAsk(tournament) {
                this.publishConfirmationTournamentId = tournament.id;
            },
            publishTournamentConfirm(tournament) {
                this.publishConfirmationTournamentId = null;
                this.setTournamentState({
                    tournamentid: tournament.id,
                    state: 'progress',
                })
            },
            deleteTournamentAsk(tournament) {
                this.deleteConfirmationTournamentId = tournament.id;
            },
            deleteTournamentConfirm(tournament) {
                this.deleteConfirmationTournamentId = null;
                this.setTournamentState({
                    tournamentid: tournament.id,
                    state: 'dumped',
                });
            }
        },
        components: {
            VkGrid,
            infoAlert,
            btnAdd,
        }
    }
</script>

<style lang="scss" scoped>
    .actions {
        & > button {
            margin-left: 0;
        }

        & > button:last-child {
            margin-right: 0;
        }
    }
</style>

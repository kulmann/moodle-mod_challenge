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
                                    button.btn.btn-default(@click="editTournament(tournament)", :disabled="stateChangeInProgress")
                                        v-icon(name="regular/edit")
                                    button.btn.btn-default(@click="createMatches(tournament)", :disabled="stateChangeInProgress")
                                        v-icon(name="users")
                                    button.btn.btn-default(@click="publishTournamentAsk(tournament)", :disabled="stateChangeInProgress || !hasMatches(tournament)")
                                        v-icon(name="rocket")
                                    button.btn.btn-default(@click="deleteTournamentAsk(tournament)", :disabled="stateChangeInProgress")
                                        v-icon(name="trash")
                            tr(v-if="publishConfirmationTournamentId === tournament.id")
                                td(colspan="3")
                                    confirmationPanel(:message="stringParams(strings.admin_tournament_publish_confirm, tournament.name)",
                                        :labelSubmit="strings.admin_btn_confirm_publish",
                                        @onSubmit="publishTournamentConfirm(tournament)",
                                        @onCancel="publishTournamentCancel()")
                            tr(v-if="deleteConfirmationTournamentId === tournament.id")
                                td(colspan="3")
                                    confirmationPanel(:message="stringParams(strings.admin_tournament_delete_confirm, tournament.name)",
                                        :labelSubmit="strings.admin_btn_confirm_delete",
                                        @onSubmit="deleteTournamentConfirm(tournament)",
                                        @onCancel="deleteTournamentCancel()")
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
    import confirmationPanel from "../helper/confirmation-panel";

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
            stateChangeInProgress() {
                return this.publishConfirmationTournamentId !== null || this.deleteConfirmationTournamentId !== null;
            },
        },
        methods: {
            ...mapActions({
                setTournamentState: 'admin/setTournamentState',
            }),
            hasMatches(tournament) {
                return tournament.has_matches;
            },
            createTournament() {
                this.$router.push({name: 'admin-tournament-edit'});
            },
            editTournament(tournament) {
                this.$router.push({name: 'admin-tournament-edit', params: {tournamentId: tournament.id}});
            },
            createMatches(tournament) {
                this.$router.push({name: 'admin-matches-edit', params: {tournamentId: tournament.id}});
            },
            publishTournamentAsk(tournament) {
                this.publishConfirmationTournamentId = tournament.id;
            },
            publishTournamentConfirm(tournament) {
                this.publishTournamentCancel();
                this.setTournamentState({
                    tournamentid: tournament.id,
                    state: 'progress',
                })
            },
            publishTournamentCancel() {
                this.publishConfirmationTournamentId = null;
            },
            deleteTournamentAsk(tournament) {
                this.deleteConfirmationTournamentId = tournament.id;
            },
            deleteTournamentConfirm(tournament) {
                this.deleteTournamentCancel();
                this.setTournamentState({
                    tournamentid: tournament.id,
                    state: 'dumped',
                });
            },
            deleteTournamentCancel() {
                this.deleteConfirmationTournamentId = null;
            }
        },
        components: {
            confirmationPanel,
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

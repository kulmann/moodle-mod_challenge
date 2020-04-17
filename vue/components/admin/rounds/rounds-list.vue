<template lang="pug">
    .uk-card.uk-card-default
        .uk-card-header
            h3 {{ strings.admin_rounds_title }}
        .uk-card-body
            p {{ strings.admin_rounds_intro }}
            table.uk-table.uk-table-small.uk-table-striped
                thead
                    tr
                        th.uk-table-shrink {{ strings.admin_rounds_list_th_no }}
                        th.uk-table-expand {{ strings.admin_rounds_list_th_name }}
                        th.uk-table-shrink {{ strings.admin_rounds_list_th_timing }}
                        th.uk-table-shrink {{ strings.admin_rounds_list_th_actions }}
                tbody
                    template(v-for="round in rounds")
                        tr.uk-text-nowrap(:key="round.number")
                            td.uk-text-center.uk-text-middle
                                b {{ round.number }}
                            td.uk-text-left.uk-text-middle {{ round.name }}
                            td.uk-text-left.uk-text-middle
                                span(v-if="isRoundScheduled(round)")
                                    span {{ getRoundTimingFrom(round) }}
                                    br
                                    span {{ getRoundTimingTo(round) }}
                                span(v-else) -
                            td.uk-text-right.uk-preserve-width
                                button.btn.btn-danger(@click="stopRoundAsk(round)", v-if="isCurrentRound(round)")
                                    v-icon(name="stop")
                                button.btn.btn-primary(@click="startRoundAsk(round)", v-else-if="isUpcomingRound(round)")
                                    v-icon(name="play")
                                button.btn.btn-default(@click="goToEditRound(round)")
                                    v-icon(name="regular/edit")
                                button.btn.btn-default(@click="deleteRoundAsk(round)", :disabled="isRoundStarted(round)")
                                    v-icon(name="trash")
                        tr(v-if="isConfirmationShown(round)")
                            td(:colspan="4").uk-table-expand
                                confirmationPanel(v-if="deleteConfirmationRoundId",
                                    :message="stringParams(strings.admin_round_delete_confirm, round.number)",
                                    :labelSubmit="strings.admin_btn_confirm_delete",
                                    @onSubmit="deleteRoundConfirm(round)",
                                    @onCancel="deleteConfirmationRoundId = null")
                                confirmationPanel(v-if="startConfirmationRoundId",
                                    :message="stringParams(strings.admin_round_start_confirm, round.number)",
                                    :labelSubmit="strings.admin_btn_confirm_start",
                                    @onSubmit="startRoundConfirm",
                                    @onCancel="startConfirmationRoundId = null")
                                confirmationPanel(v-if="stopConfirmationRoundId",
                                    :message="stringParams(strings.admin_round_stop_confirm, round.number)",
                                    :labelSubmit="strings.admin_btn_confirm_stop",
                                    @onSubmit="stopRoundConfirm",
                                    @onCancel="stopConfirmationRoundId = null")
            btnAdd(@click="goToCreateRound")
</template>

<script>
    import {mapActions, mapState} from 'vuex';
    import moment from 'moment';
    import first from 'lodash/first';
    import LangMixins from '../../../mixins/lang-mixins';
    import TimeMixins from '../../../mixins/time-mixins';
    import infoAlert from '../../helper/info-alert';
    import btnAdd from '../btn-add';
    import VkGrid from "vuikit/src/library/grid/components/grid";
    import ConfirmationPanel from "../../helper/confirmation-panel";

    export default {
        mixins: [LangMixins, TimeMixins],
        props: {
            rounds: {
                type: Array,
                required: true
            },
            categories: {
                type: Array,
                required: true
            },
            mdlCategories: {
                type: Array,
                required: true
            }
        },
        data() {
            return {
                deleteConfirmationRoundId: null,
                startConfirmationRoundId: null,
                stopConfirmationRoundId: null,
            }
        },
        computed: {
            ...mapState([
                'contextID',
                'strings',
                'now',
            ]),
            currentRound() {
                return first(this.rounds.filter(round => {
                    return this.isRoundStarted(round) && !this.isRoundEnded(round);
                }));
            },
            upcomingRound() {
                return first(this.rounds.filter(round => {
                    return !this.isRoundStarted(round);
                }));
            }
        },
        methods: {
            ...mapActions({
                deleteRound: 'admin/deleteRound',
                startRound: 'admin/startRound',
                stopRound: 'admin/stopRound',
            }),
            isCurrentRound(round) {
                return this.currentRound && round.id === this.currentRound.id;
            },
            isUpcomingRound(round) {
                return this.upcomingRound && round.id === this.upcomingRound.id;
            },
            isRoundStarted(round) {
                return round.timestart !== 0 && round.timestart <= moment().unix();
            },
            isRoundEnded(round) {
                return round.timeend !== 0 && round.timeend <= moment().unix();
            },
            isRoundScheduled(round) {
                return round.timestart !== 0;
            },
            getRoundTimingFrom(round) {
                return this.stringParams(this.strings.admin_rounds_list_timing_from, this.formDateTime(round.timestart));
            },
            getRoundTimingTo(round) {
                return this.stringParams(this.strings.admin_rounds_list_timing_to, this.formDateTime(round.timeend));
            },
            // actions
            goToCreateRound() {
                this.$router.push({name: 'admin-round-edit', params: {roundId: 0}});
            },
            goToEditRound(round) {
                this.$router.push({name: 'admin-round-edit', params: {roundId: round.id}});
            },
            deleteRoundAsk(round) {
                this.deleteConfirmationRoundId = round.id;
            },
            deleteRoundConfirm(round) {
                this.deleteRound({roundid: round.id}).then(this.closeConfirmations);
            },
            startRoundAsk(round) {
                this.startConfirmationRoundId = round.id;
            },
            startRoundConfirm() {
                this.startRound({}).then(this.closeConfirmations);
            },
            stopRoundAsk(round) {
                this.stopConfirmationRoundId = round.id;
            },
            stopRoundConfirm() {
                this.stopRound({}).then(this.closeConfirmations);
            },
            // confirmation panels
            isConfirmationShown(round) {
                return this.deleteConfirmationRoundId === round.id
                    || this.startConfirmationRoundId === round.id
                    || this.stopConfirmationRoundId === round.id;
            },
            closeConfirmations() {
                this.deleteConfirmationRoundId = null;
                this.startConfirmationRoundId = null;
                this.stopConfirmationRoundId = null;
            },
        },
        components: {
            ConfirmationPanel,
            VkGrid,
            infoAlert,
            btnAdd,
        }
    }
</script>

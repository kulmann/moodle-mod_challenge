<template lang="pug">
  .uk-card.uk-card-default
    .uk-card-header
      h3 {{ strings.admin_rounds_title }}
    .uk-card-body
      p {{ strings.admin_rounds_intro }}
      table.uk-table.uk-table-small.uk-table-striped(v-if="rounds.length > 0")
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
                button.btn.btn-primary(@click="pickSchedule(round)", v-if="!isRoundStarted(round)")
                  v-icon(name="clock")
                button.btn.btn-danger(@click="stopRoundAsk(round)", v-if="isActiveRound(round)")
                  v-icon(name="stop")
                button.btn.btn-success(@click="goToRoundResults(round)", :disabled="!isRoundStarted(round)")
                  v-icon(name="chart-line")
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
                confirmationPanel(v-if="stopConfirmationRoundId",
                  :message="stringParams(strings.admin_round_stop_confirm, round.number)",
                  :labelSubmit="strings.admin_btn_confirm_stop",
                  @onSubmit="stopRoundConfirm(round)",
                  @onCancel="stopConfirmationRoundId = null")
      info-alert(v-else, :message="strings.admin_rounds_empty")
      btn-add(@click="goToCreateRound")
      datetime-popup(
        v-if="datepicker.show",
        :key="datepicker.key",
        type="datetime",
        :datetime="datepicker.value",
        :title="datepicker.title",
        :phrases="datepickerPhrases"
        @cancel="onResetDatepicker",
        @confirm="onInputDatepicker"
      )
</template>

<script>
import { mapActions, mapState } from "vuex";
import moment from "moment";
import LangMixins from "../../../mixins/lang-mixins";
import TimeMixins from "../../../mixins/time-mixins";
import InfoAlert from "../../helper/info-alert";
import BtnAdd from "../btn-add";
import VkGrid from "vuikit/src/library/grid/components/grid";
import ConfirmationPanel from "../../helper/confirmation-panel";
import { DatetimePopup } from "vue-datetime";
import { DateTime } from "luxon";

export default {
  mixins: [LangMixins, TimeMixins],
  props: {
    rounds: {
      type: Array,
      required: true,
    },
    categories: {
      type: Array,
      required: true,
    },
    mdlCategories: {
      type: Array,
      required: true,
    },
  },
  data() {
    return {
      deleteConfirmationRoundId: null,
      stopConfirmationRoundId: null,
      datepicker: {
        key: "empty",
        show: false,
        isStart: true,
        value: null,
        round: null,
        title: null,
      },
    };
  },
  computed: {
    ...mapState(["contextID", "strings", "now", "game"]),
    datepickerPhrases() {
      return {
        ok: this.strings.admin_btn_datepicker_submit,
        cancel: this.strings.admin_btn_datepicker_cancel,
      };
    },
  },
  methods: {
    ...mapActions({
      deleteRound: "admin/deleteRound",
      scheduleRound: "admin/scheduleRound",
      stopRound: "admin/stopRound",
    }),
    isActiveRound(round) {
      return this.isRoundStarted(round) && !this.isRoundEnded(round);
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
      return this.stringParams(
        this.strings.admin_rounds_list_timing_from,
        this.formDateTime(round.timestart)
      );
    },
    getRoundTimingTo(round) {
      return this.stringParams(
        this.strings.admin_rounds_list_timing_to,
        this.formDateTime(round.timeend)
      );
    },
    // actions
    goToRoundResults(round) {
      this.$router.push({
        name: "admin-round-results",
        params: { roundId: round.id },
      });
    },
    goToCreateRound() {
      this.$router.push({ name: "admin-round-edit", params: { roundId: 0 } });
    },
    goToEditRound(round) {
      this.$router.push({
        name: "admin-round-edit",
        params: { roundId: round.id },
      });
    },
    deleteRoundAsk(round) {
      this.deleteConfirmationRoundId = round.id;
    },
    deleteRoundConfirm(round) {
      this.deleteRound({ roundid: round.id }).then(this.closeConfirmations);
    },
    stopRoundAsk(round) {
      this.stopConfirmationRoundId = round.id;
    },
    stopRoundConfirm(round) {
      this.stopRound({ roundid: round.id }).then(this.closeConfirmations);
    },
    // date picker
    pickSchedule(round) {
      this.datepicker = {
        key: "start",
        show: true,
        isStart: true,
        value: DateTime.local(),
        round,
        title: this.strings.admin_round_datepicker_start,
      };
    },
    onResetDatepicker() {
      this.datepicker = {
        key: "empty",
        show: false,
        isStart: true,
        value: null,
        round: null,
        title: null,
      };
    },
    onInputDatepicker(value) {
      const round = this.datepicker.round;
      round.timestart = Math.round(value.toSeconds());
      round.timeend =
        Math.round(value.toSeconds()) + this.game.round_duration_seconds;
      this.onResetDatepicker();
      this.scheduleRound({
        roundid: round.id,
        timestart: round.timestart,
        timeend: round.timeend,
      });
    },
    // confirmation panels
    isConfirmationShown(round) {
      return (
        this.deleteConfirmationRoundId === round.id ||
        this.stopConfirmationRoundId === round.id
      );
    },
    closeConfirmations() {
      this.deleteConfirmationRoundId = null;
      this.stopConfirmationRoundId = null;
    },
  },
  components: {
    ConfirmationPanel,
    VkGrid,
    InfoAlert,
    BtnAdd,
    DatetimePopup,
  },
};
</script>

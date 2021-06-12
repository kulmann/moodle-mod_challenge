<template lang="pug">
div
  .uk-card.uk-card-default
    .uk-card-header
      h3 {{ strings.admin_users_participants_title }}
    .uk-card-body
      p.uk-text-meta {{ strings.admin_users_participants_intro }}
      table.uk-table.uk-table-small
        thead
          tr
            th {{ strings.admin_users_participants_thead_user }}
            th {{ strings.admin_users_participants_thead_status }}
            th.uk-text-center(
              v-for="round in startedRounds",
              :key="`thead-round-${round.number}`"
            ) {{ strings.admin_users_participants_thead_round | stringParams(round.number) }}
        tbody
          tr(
            v-for="participant in participants",
            :key="`participant-${participant.id}`"
          )
            td
              user-avatar(:size="30", :user="participant")
              span {{ participant.fullname }}
            td
              user-status-edit(
                :id="participant.id",
                :value="participant.status",
                @input="saveUserStatus"
              )
            td.uk-text-center(
              v-for="round in startedRounds",
              :key="`participant-${participant.id}-round-${round.number}`"
            )
              v-icon(
                :name="getAttendanceIcon(participant, round)",
                :class="[`round-cell-${getAttendanceState(participant, round)}`]"
              )
  .uk-card.uk-card-default.uk-margin-top
    .uk-card-header
      h3 {{ strings.admin_users_teachers_title }}
    .uk-card-body
      p.uk-text-meta {{ strings.admin_users_teachers_intro }}
      .uk-flex
        span.uk-flex.uk-flex-middle.uk-margin-small-right(
          v-for="teacher in teachers",
          :key="`teacher-${teacher.id}`"
        )
          user-avatar(:size="30", :user="teacher")
          span {{ teacher.fullname }}
</template>

<script>
import { mapActions, mapState } from "vuex";
import LangMixins from "../../../mixins/lang-mixins";
import UserAvatar from "../../helper/user-avatar";
import UserStatusEdit from "./user-status-edit";

const PROGRESS_FINISHED = "finished";
const PROGRESS_PENDING = "pending";
const PROGRESS_SKIPPED = "skipped";

export default {
  components: { UserStatusEdit, UserAvatar },
  mixins: [LangMixins],
  computed: {
    ...mapState(["strings", "rounds"]),
    ...mapState("admin", ["users"]),
    startedRounds() {
      return this.rounds
        .filter((r) => r.timestart > 0)
        .sort((r1, r2) => r1.number - r2.number);
    },
    teachers() {
      return this.users
        .filter((u) => u.type === "teacher")
        .sort((u1, u2) => u1.fullname.localeCompare(u2.fullname));
    },
    participants() {
      return this.users
        .filter((u) => u.type === "participant")
        .sort((u1, u2) => u1.fullname.localeCompare(u2.fullname));
    },
  },
  methods: {
    ...mapActions("admin", ["saveUserStatus"]),
    getAttendanceState(participant, round) {
      const attendedRoundIds = participant.attended_rounds
        .split(",")
        .map((value) => parseInt(value));
      if (attendedRoundIds.includes(round.id)) {
        return PROGRESS_FINISHED;
      }
      if (round.timeend > Math.floor(Date.now() / 1000)) {
        return PROGRESS_PENDING;
      }
      return PROGRESS_SKIPPED;
    },
    getAttendanceIcon(participant, round) {
      const state = this.getAttendanceState(participant, round);
      switch (state) {
        case PROGRESS_FINISHED:
          return "check-circle";
        case PROGRESS_PENDING:
          return "hourglass";
        case PROGRESS_SKIPPED:
        default:
          return "times-circle";
      }
    },
  },
};
</script>

<style lang="scss" scoped>
.round-cell-finished {
  color: green;
}
.round-cell-pending {
  color: orange;
}
.round-cell-skipped {
  color: maroon;
}
</style>

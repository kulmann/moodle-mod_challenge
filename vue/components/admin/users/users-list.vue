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
import { mapState } from "vuex";
import LangMixins from "../../../mixins/lang-mixins";
import UserAvatar from "../../helper/user-avatar";

const finished = "finished";
const pending = "pending";
const skipped = "skipped";

export default {
  components: { UserAvatar },
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
    getAttendanceState(participant, round) {
      const attendedRoundIds = participant.attended_rounds
        .split(",")
        .map((value) => parseInt(value));
      if (attendedRoundIds.includes(round.id)) {
        return finished;
      }
      if (round.timeend > Math.floor(Date.now() / 1000)) {
        return pending;
      }
      return skipped;
    },
    getAttendanceIcon(participant, round) {
      const state = this.getAttendanceState(participant, round);
      switch (state) {
        case finished:
          return "check-circle";
        case pending:
          return "hourglass";
        case skipped:
        default:
          return "times-circle";
      }
    },
  },
  mounted() {
    console.log(this.users);
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

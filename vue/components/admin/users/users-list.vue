<template lang="pug">
div
  .uk-card.uk-card-default
    .uk-card-header
      h3 {{ strings.admin_users_participants_title }}
    .uk-card-body
      p.uk-text-meta {{ strings.admin_users_participants_intro }}
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

export default {
  components: { UserAvatar },
  mixins: [LangMixins],
  computed: {
    ...mapState(["strings"]),
    ...mapState("admin", ["users"]),
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
  mounted() {
    console.log(this.users);
  },
};
</script>

<template lang="pug">
    div
        failureAlert(v-if="!validUserSelection", :message="strings.admin_tournament_match_invalid_users")
        template(v-else)
            button.btn.btn-primary(@click="generateMatches") {{ strings.admin_btn_generate }}
            template(v-if="matches.length === 0")
                h4 {{ strings.admin_tournament_match_none_title }}
                p {{ strings.admin_tournament_match_none_msg }}
            template(v-else)
                h4 {{ strings.admin_tournament_match_done_title }}
                p {{ strings.admin_tournament_match_done_msg }}
                table.uk-table.uk-table-striped
                    thead
                        tr
                            th.uk-table-shrink {{ strings.admin_tournament_match_table_number }}
                            th.uk-table-expand {{ strings.admin_tournament_match_table_participant }}
                    tbody
                        tr(v-for="(match, index) in matches", :key="'match-' + index")
                            td.uk-table-middle.uk-text-center
                                b(style="font-size: 1.5em;") {{ index + 1 }}
                            td.uk-table-middle
                                div
                                    userAvatar(:size="20", :user="getMdlUser(match.mdl_user_1)")
                                    span {{ getMdlUser(match.mdl_user_1).firstname + ' ' + getMdlUser(match.mdl_user_1).lastname }}
                                div.uk-margin-small-top
                                    userAvatar(:size="20", :user="getMdlUser(match.mdl_user_2)")
                                    span {{ getMdlUser(match.mdl_user_2).firstname + ' ' + getMdlUser(match.mdl_user_2).lastname }}
</template>
<script>
    import _ from 'lodash';
    import {mapState, mapGetters, mapActions} from 'vuex';
    import failureAlert from "../../helper/failure-alert";
    import UserAvatar from "../../helper/user-avatar";

    export default {
        props: {
            tournament: Object,
            participants: Array,
            value: Array,
        },
        data() {
            return {
                matches: [],
            }
        },
        computed: {
            ...mapState([
                'strings',
            ]),
            ...mapGetters([
                'getMdlUser'
            ]),
            validUserSelection() {
                const count = this.participants.length;
                return count > 0 && count % 2 === 0;
            },
        },
        methods: {
            generateMatches() {
                let mdlUserIds = _.shuffle(_.map(this.participants, p => p.id));
                let matches = [];
                while (mdlUserIds.length > 1) {
                    matches.push({
                        mdl_user_1: mdlUserIds.pop(),
                        mdl_user_2: mdlUserIds.pop(),
                    });
                }
                this.matches = matches;
                this.$emit('input', this.matches);
            },
        },
        mounted() {
            this.matches = this.value;
        },
        watch: {
            value() {
                this.matches = this.value;
            },
        },
        components: {UserAvatar, failureAlert},
    }
</script>

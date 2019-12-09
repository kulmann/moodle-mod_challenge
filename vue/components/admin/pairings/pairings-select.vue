<template lang="pug">
    div
        failureAlert(v-if="!validUserSelection", :message="strings.admin_tournament_pairing_invalid_users")
        template(v-else)
            button.btn.btn-primary(@click="generatePairings") {{ strings.admin_btn_generate }}
            template(v-if="pairings.length === 0")
                h4 {{ strings.admin_tournament_pairing_none_title }}
                p {{ strings.admin_tournament_pairing_none_msg }}
            template(v-else)
                h4 {{ strings.admin_tournament_pairing_done_title }}
                p {{ strings.admin_tournament_pairing_done_msg }}
                table.uk-table.uk-table-striped
                    thead
                        tr
                            th.uk-table-shrink {{ strings.admin_tournament_pairing_table_number }}
                            th.uk-table-expand {{ strings.admin_tournament_pairing_table_participant }}
                    tbody
                        tr(v-for="(pair, index) in pairings", :key="'pair-' + index")
                            td.uk-table-middle.uk-text-center
                                b(style="font-size: 1.5em;") {{ index + 1 }}
                            td.uk-table-middle
                                div
                                    userAvatar(:size="20", :user="getMdlUser(pair.mdl_user_1)")
                                    span {{ getMdlUser(pair.mdl_user_1).firstname + ' ' + getMdlUser(pair.mdl_user_1).lastname }}
                                div.uk-margin-small-top
                                    userAvatar(:size="20", :user="getMdlUser(pair.mdl_user_2)")
                                    span {{ getMdlUser(pair.mdl_user_2).firstname + ' ' + getMdlUser(pair.mdl_user_2).lastname }}
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
                pairings: [],
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
            ...mapActions({
                savePairings: 'admin/savePairings',
            }),
            generatePairings() {
                let mdlUserIds = _.shuffle(_.map(this.participants, p => p.id));
                let pairings = [];
                while (mdlUserIds.length > 1) {
                    pairings.push({
                        mdl_user_1: mdlUserIds.pop(),
                        mdl_user_2: mdlUserIds.pop(),
                    });
                }
                this.pairings = pairings;
                this.$emit('input', this.pairings);
            },
        },
        mounted() {
            this.pairings = this.value;
        },
        watch: {
            value() {
                this.pairings = this.value;
            },
        },
        components: {UserAvatar, failureAlert},
    }
</script>

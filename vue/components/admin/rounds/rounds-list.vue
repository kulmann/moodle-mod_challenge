<template lang="pug">
    .uk-card.uk-card-default
        .uk-card-header
            h3 {{ strings.admin_rounds_title }}
        .uk-card-body
            p {{ strings.admin_rounds_intro }}
            btnAdd(@click="createRound")
            table.uk-table.uk-table-small.uk-table-striped
                tbody
                    template(v-for="round in rounds")
                        tr.uk-text-nowrap(:key="round.number")
                            td.uk-table-shrink.uk-text-center.uk-text-middle
                                b {{ round.number }}
                            td.uk-table-auto.uk-text-left.uk-text-middle {{ round.name }}
                            td.actions.uk-table-shrink.uk-preserve-width
                                button.btn.btn-default(@click="editRound(round)")
                                    v-icon(name="regular/edit")
                                button.btn.btn-default(@click="deleteRoundAsk(round)")
                                    v-icon(name="trash")
                        tr(v-if="deleteConfirmationRoundId === round.id")
                            td(colspan="3")
                                confirmationPanel(:message="stringParams(strings.admin_round_delete_confirm, round.name)",
                                    :labelSubmit="strings.admin_btn_confirm_delete",
                                    @onSubmit="deleteRoundConfirm(round)",
                                    @onCancel="deleteRoundCancel()")
            btnAdd(@click="createRound")
</template>

<script>
    import {mapActions, mapState} from 'vuex';
    import mixins from '../../mixins';
    import infoAlert from '../helper/info-alert';
    import btnAdd from './btn-add';
    import VkGrid from "vuikit/src/library/grid/components/grid";
    import ConfirmationPanel from "../helper/confirmation-panel";

    export default {
        mixins: [mixins],
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
            }
        },
        computed: {
            ...mapState([
                'contextID',
                'strings',
            ])
        },
        methods: {
            ...mapActions({
                deleteRound: 'admin/deleteRound',
            }),
            createRound() {
                this.$router.push({name: 'admin-round-edit'});
            },
            editRound(round) {
                this.$router.push({name: 'admin-round-edit', params: {roundId: round.id}});
            },
            deleteRoundAsk(round) {
                this.deleteConfirmationRoundId = round.id;
            },
            deleteRoundConfirm(round) {
                this.deleteRoundCancel();
                this.deleteRound({roundid: round.id});
            },
            deleteRoundCancel() {
                this.deleteConfirmationRoundId = null;
            }
        },
        components: {
            ConfirmationPanel,
            VkGrid,
            infoAlert,
            btnAdd,
        }
    }
</script>

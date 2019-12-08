<template lang="pug">
    .uk-card.uk-card-default
        .uk-card-header
            h3 {{ strings.admin_levels_title }}
        .uk-card-body
            template(v-if="levels.length === 0")
                infoAlert(:message="strings.admin_levels_none")
                btnAdd(@click="createLevel")
            template(v-else)
                p {{ strings.admin_levels_intro }}
                btnAdd(@click="createLevel")
                table.uk-table.uk-table-small.uk-table-striped
                    tbody
                        template(v-for="level in sortedLevels")
                            tr.uk-text-nowrap(:key="level.position")
                                td.uk-table-shrink.uk-text-center.uk-text-middle
                                    b {{ level.position + 1 }}
                                td.uk-table-auto.uk-text-left.uk-text-middle {{ level.name }}
                                td.actions.uk-table-shrink.uk-preserve-width
                                    button.btn.btn-default(@click="editLevel(level)")
                                        v-icon(name="regular/edit")
                                    button.btn.btn-default(@click="moveLevel(level, 1)", :disabled="level.position === (levels.length - 1)")
                                        v-icon(name="arrow-down")
                                    button.btn.btn-default(@click="moveLevel(level, -1)", :disabled="level.position === 0")
                                        v-icon(name="arrow-up")
                                    button.btn.btn-default(@click="deleteLevelAsk(level)")
                                        v-icon(name="trash")
                            tr(v-if="deleteConfirmationLevelId === level.id")
                                td(colspan="3")
                                    confirmationPanel(:message="stringParams(strings.admin_level_delete_confirm, level.name)",
                                        :labelSubmit="strings.admin_btn_confirm_delete",
                                        @onSubmit="deleteLevelConfirm(level)",
                                        @onCancel="deleteLevelCancel()")
                btnAdd(@click="createLevel")
</template>

<script>
    import {mapActions, mapState} from 'vuex';
    import _ from 'lodash';
    import mixins from '../../mixins';
    import infoAlert from '../helper/info-alert';
    import btnAdd from './btn-add';
    import VkGrid from "vuikit/src/library/grid/components/grid";
    import ConfirmationPanel from "../helper/confirmation-panel";

    export default {
        mixins: [mixins],
        props: {
            levels: Array,
        },
        data() {
            return {
                deleteConfirmationLevelId: null,
            }
        },
        computed: {
            ...mapState([
                'contextID',
                'strings',
            ]),
            sortedLevels() {
                return _.sortBy(this.levels, ['position']);
            }
        },
        methods: {
            ...mapActions({
                changeLevelPosition: 'admin/changeLevelPosition',
                deleteLevel: 'admin/deleteLevel',
            }),
            isSafeSpot(level) {
                return level.safe_spot;
            },
            createLevel() {
                this.$router.push({name: 'admin-level-edit'});
            },
            editLevel(level) {
                this.$router.push({name: 'admin-level-edit', params: {levelId: level.id}});
            },
            moveLevel(level, delta) {
                this.changeLevelPosition({
                    levelid: level.id,
                    delta: delta,
                });
            },
            deleteLevelAsk(level) {
                this.deleteConfirmationLevelId = level.id;
            },
            deleteLevelConfirm(level) {
                this.deleteLevelCancel();
                this.deleteLevel({
                    levelid: level.id
                });
            },
            deleteLevelCancel() {
                this.deleteConfirmationLevelId = null;
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

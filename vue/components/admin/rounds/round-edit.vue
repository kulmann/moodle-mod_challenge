<template lang="pug">
    .uk-card.uk-card-default
        .uk-card-body(v-if="data === null || categories === null")
            loadingAlert(:message="strings.admin_round_loading")
        template(v-else)
            .uk-card-body
                form.uk-form-stacked(@submit.prevent="save")
                    h3 {{ strings['admin_round_edit_title_' + (data.id ? 'edit' : 'add')] | stringParams(data.number) }}

                    .uk-margin-small
                        label.uk-form-label {{ strings.admin_round_lbl_name }}
                        .uk-form-controls
                            input.uk-input(v-model="data.name", required)

                    h3 {{ strings.admin_round_categories_title }}
                    p {{ strings.admin_round_edit_description }}
                    .uk-margin-small(v-for="(category, index) in activeCategories", :key="index")
                        label.uk-form-label {{ getCategoryLabel(category, index) }}
                        .uk-form-controls
                            .uk-flex
                                select.uk-select(v-model="category.mdl_category", :disabled="!!category.id")
                                    option(:disabled="true", value="") {{ strings.admin_round_lbl_category_please_select }}
                                    option(v-for="mdl_category in mdlCategories", :key="mdl_category.category_id",
                                        v-bind:value="mdl_category.category_id", :disabled="!mdl_category.category_id",
                                        v-html="mdl_category.category_name")
                                button.btn.btn-default(type="button", @click="removeCategory(category)")
                                    v-icon(name="trash")
                    btnAdd(@click="createCategory", align="left")
            .uk-card-footer.uk-text-right
                button.btn.btn-primary(@click="save()", :disabled="saving")
                    v-icon(name="save").uk-margin-small-right
                    span {{ strings.admin_btn_save }}
                button.btn.btn-default(@click="goToRoundList()", :disabled="saving").uk-margin-small-left
                    v-icon(name="ban").uk-margin-small-right
                    span {{ strings.admin_btn_cancel }}
                .uk-alert.uk-alert-primary.uk-text-center(uk-alert, v-if="saving")
                    p
                        span {{ strings.admin_round_msg_saving }}
                        loadingIcon

</template>

<script>
    import {mapActions, mapState} from 'vuex';
    import langMixins from '../../../mixins/lang-mixins';
    import isNil from 'lodash/isNil';
    import map from 'lodash/map';
    import concat from 'lodash/concat';
    import find from 'lodash/find';
    import constants from '../../../constants';
    import btnAdd from '../btn-add';
    import loadingAlert from "../../helper/loading-alert";
    import loadingIcon from "../../helper/loading-icon";
    import VkNotification from "vuikit/src/library/notification/components/notification";

    export default {
        mixins: [langMixins],
        props: {
            round: {
                type: Object,
                required: false
            },
            categories: {
                type: Array,
                required: true
            },
            mdlCategories: {
                type: Array,
                required: true
            },
            rounds: {
                type: Array,
                required: true
            }
        },
        data() {
            return {
                data: null,
                saving: false,
                categoryChanges: {
                    deleted: [],
                    added: []
                }
            }
        },
        computed: {
            ...mapState([
                'strings',
                'game'
            ]),
            roundCategories() {
                if (this.data.id === null) {
                    // new rounds don't have any already saved categories
                    return [];
                }
                return this.categories.filter(category => {
                    const firstRound = this.getRoundById(category.round_first);
                    const lastRound = category.round_last === 0 ? null : this.getRoundById(category.round_last);
                    return firstRound.number <= this.data.number && (lastRound === null ? true : lastRound.number >= this.data.number);
                });
            },
            activeCategories() {
                const categories = this.roundCategories.filter(category => {
                    return this.categoryChanges.deleted.findIndex(c => c.id === category.id) === -1;
                });
                categories.push(...this.categoryChanges.added);
                return categories;
            }
        },
        methods: {
            ...mapActions({
                saveRound: 'admin/saveRound',
                fetchMdlCategories: 'admin/fetchMdlCategories'
            }),
            initRoundData(round) {
                if (isNil(round)) {
                    this.data = {
                        id: null,
                        game: this.game.id,
                        number: this.rounds.length + 1,
                        name: '',
                    };
                } else {
                    this.data = round;
                }
            },
            getRoundById(roundId) {
                return find(this.rounds, round => round.id === roundId);
            },
            getCategoryLabel(category, index) {
                if (category.id === null) {
                    return this.stringParams(this.strings.admin_round_lbl_category_new, index + 1);
                }
                const roundFirst = this.getRoundById(category.round_first);
                if (category.round_last === 0) {
                    return this.stringParams(this.strings.admin_round_lbl_category_open, {number: index + 1, round_first_number: roundFirst.number});
                }
                if (category.round_first === category.round_last) {
                    return this.stringParams(this.strings.admin_round_lbl_category_closed_same, {number: index + 1, round_first_number: roundFirst.number});
                }
                const roundLast = this.getRoundById(category.round_last);
                return this.stringParams(this.strings.admin_round_lbl_category_closed_range, {
                    number: index + 1,
                    round_first_number: roundFirst.number,
                    round_last_number: roundLast.number
                });
            },
            createCategory() {
                this.categoryChanges.added = concat(this.categoryChanges.added, [{
                    id: null,
                    mdl_category: null
                }]);
            },
            removeCategory(category) {
                if (category.id) {
                    this.categoryChanges.deleted = concat(this.categoryChanges.deleted, [category]);
                } else {
                    this.categoryChanges.added = this.categoryChanges.added.filter(c => c !== category);
                }
            },
            goToRoundList() {
                this.$router.push({name: constants.ROUTE_ADMIN_ROUNDS});
            },
            save() {
                const addedCategories = map(this.categoryChanges.added, transformCategoryToDTO);
                const deletedCategories = map(this.categoryChanges.deleted, transformCategoryToDTO);
                let result = {
                    roundid: (this.data.id || 0),
                    name: this.data.name,
                    addedcategories: addedCategories,
                    deletedcategories: deletedCategories
                };
                this.saving = true;
                this.saveRound(result)
                    .then((successful) => {
                        this.saving = false;
                        if (successful) {
                            this.goToRoundList();
                        }
                    });
            }
        },
        mounted() {
            this.fetchMdlCategories();
            this.initRoundData(this.round);
        },
        watch: {
            round: function (round) {
                this.initRoundData(round);
            },
            categories: function (categories) {
                this.categories = categories;
            },
        },
        components: {
            btnAdd,
            loadingAlert,
            loadingIcon,
            VkNotification,
        },
    }

    function transformCategoryToDTO(category) {
        return {
            categoryid: category.id,
            mdlcategory: category.mdl_category,
            subcategories: category.subcategories || true,
        };
    }
</script>

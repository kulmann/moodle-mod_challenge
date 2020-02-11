<template lang="pug">
    .uk-card.uk-card-default
        .uk-card-body(v-if="data === null || categories === null")
            loadingAlert(:message="strings.admin_round_loading")
        template(v-else)
            .uk-card-body
                form.uk-form-stacked
                    h3 {{ strings.admin_round_title_edit | stringParams(data.number) }}

                    vk-grid(matched).uk-grid-divider
                        div.uk-width-1-2
                            .uk-margin-small
                                label.uk-form-label {{ strings.admin_level_lbl_name }}
                                .uk-form-controls
                                    input.uk-input(v-model="data.name", :placeholder="strings.admin_level_lbl_name")
                            .uk-margin-small
                                label.uk-form-label {{ strings.admin_level_lbl_bgcolor }}
                                .uk-form-controls
                                    input.uk-input(v-model="data.bgcolor", :placeholder="strings.admin_level_lbl_bgcolor")
                                    i(v-html="strings.admin_level_lbl_bgcolor_help")
                            .uk-margin-small
                                label.uk-form-label {{ strings.admin_level_lbl_image }}
                                .uk-form-controls
                                    picture-input(:width="500",
                                        :height="300",
                                        :removable="true",
                                        button-class="btn btn-primary",
                                        removeButtonClass="btn btn-danger",
                                        :customStrings="{drag: strings.admin_level_lbl_image_drag, change: strings.admin_level_lbl_image_change, remove: strings.admin_level_lbl_image_remove}",
                                        :prefill="data.imageurl",
                                        @change="onImageSelected",
                                        @remove="onImageRemoved")
                        div.uk-width-1-2
                            level(:level="levelPreview", :strings="strings", :game="game")

                    h3.uk-margin-large-top {{ strings.admin_level_lbl_categories }}
                    .uk-margin-small(v-for="(category, index) in categories", :key="index")
                        label.uk-form-label {{ strings.admin_level_lbl_category | stringParams(index + 1) }}
                        .uk-form-controls
                            .uk-flex
                                select.uk-select(v-model="category.mdl_category")
                                    option(:disabled="true", value="") {{ strings.admin_level_lbl_category_please_select }}
                                    option(v-for="mdl_category in mdl_categories", :key="mdl_category.category_id",
                                        v-bind:value="mdl_category.category_id", :disabled="!mdl_category.category_id",
                                        v-html="mdl_category.category_name")
                                button.btn.btn-default(type="button", @click="removeCategory(index)")
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
    import mixins from '../../../mixins';
    import _ from 'lodash';
    import constants from '../../../constants';
    import btnAdd from '../btn-add';
    import loadingAlert from "../../helper/loading-alert";
    import loadingIcon from "../../helper/loading-icon";
    import VkNotification from "vuikit/src/library/notification/components/notification";

    export default {
        mixins: [mixins],
        props: {
            round: {
                type: Object,
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
                data: null,
                saving: false,
            }
        },
        computed: {
            ...mapState([
                'strings',
                'game',
            ]),
            ...mapState('admin', [
                'rounds'
            ]),
        },
        methods: {
            ...mapActions({
                saveRound: 'admin/saveRound',
            }),
            initRoundData(round) {
                if (round === null) {
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
            goToRoundList() {
                this.$router.push({name: constants.ROUTE_ADMIN_ROUNDS});
            },
            save() {
                let categories = _.map(this.selectedCategories, function (category) {
                    return {
                        categoryid: category.id,
                        mdlcategory: category.mdl_category,
                        subcategories: category.subcategories,
                    };
                });
                let result = {
                    levelid: (this.data.id || 0),
                    name: this.data.name,
                    bgcolor: this.data.bgcolor,
                    categories: categories,
                    image: this.data.image,
                    imgmimetype: (this.imageMimetype ? this.imageMimetype : ''),
                    imgcontent: (this.imageContent ? this.imageContent : ''),
                };
                this.saving = true;
                this.saveLevel(result)
                    .then((successful) => {
                        this.saving = false;
                        if (successful) {
                            this.goToLevelList();
                        }
                    });
            },
            onImageSelected(image) {
                this.imageBase64 = image;
                let result = image.split(',');
                this.imageMimetype = result[0].replace('data:', '').replace(';base64', '');
                this.imageContent = result[1];
            },
            onImageRemoved() {
                this.imageBase64 = null;
                this.imageMimetype = null;
                this.imageContent = null;
                this.data.image = null;
                this.data.imageurl = null;
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
</script>

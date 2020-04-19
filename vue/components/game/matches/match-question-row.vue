<template lang="pug">
    router-link(:to="{name: 'player-question-play', params: {matchId: match.id, questionNumber: question.number}}")
        vk-grid(matched, :class="{'_pointer': !isQuestionAnswered}").uk-flex-middle
            .uk-width-1-5
                v-icon(:name="leftIcon", :scale="2", :style="leftStyle")
            .uk-width-3-5
                .question-tile.uk-text-center.uk-text-middle
                    template(v-if="isQuestionAnswered")
                        span(v-if="mdlQuestion", v-html="mdlQuestion.questiontext")
                        loadingIcon(v-else)
                    span(v-else) {{ strings.game_match_lbl_question | stringParams(question.number) }}
            .uk-width-1-5
                v-icon(:name="rightIcon", :scale="2", :style="rightStyle")
</template>

<script>
    import first from 'lodash/first';
    import isNil from 'lodash/isNil';
    import langMixins from '../../../mixins/lang-mixins';
    import {mapState, mapActions} from 'vuex';
    import LoadingIcon from "../../helper/loading-icon";

    export default {
        mixins: [langMixins],
        props: {
            question: {
                type: Object,
                required: true
            },
            match: {
                type: Object,
                required: true
            },
            mdlUserLeft: {
                type: Number,
                required: true
            },
            mdlUserRight: {
                type: Number,
                required: true
            },
            questions: {
                type: Array,
                required: true
            },
            ownUserId: {
                type: Number,
                required: true
            },
        },
        data () {
            return {
                mdlQuestion: null,
            }
        },
        computed: {
            ...mapState(['strings', 'game']),
            leftQuestion() {
                return this.getQuestionByUser(this.mdlUserLeft);
            },
            leftIcon() {
                return this.getIconByQuestion(this.leftQuestion);
            },
            leftStyle() {
                return this.getStyleByQuestion(this.leftQuestion);
            },
            rightQuestion() {
                return this.getQuestionByUser(this.mdlUserRight);
            },
            rightIcon() {
                return this.getIconByQuestion(this.rightQuestion);
            },
            rightStyle() {
                return this.getStyleByQuestion(this.rightQuestion);
            },
            ownQuestion() {
                return this.getQuestionByUser(this.ownUserId);
            },
            isQuestionAnswered() {
                return !isNil(this.ownQuestion) && this.ownQuestion.finished;
            },
        },
        methods: {
            ...mapActions({
                fetchMdlQuestion: 'player/fetchMdlQuestion',
            }),
            getQuestionByUser(mdlUserId) {
                return first(this.questions.filter(q => q.mdl_user === mdlUserId));
            },
            getIconByQuestion(question) {
                if (isNil(question) || !question.finished) {
                    return "play-circle";
                } else {
                    if (question.correct) {
                        return "check-circle";
                    } else {
                        return "times-circle";
                    }
                }
            },
            getStyleByQuestion(question) {
                let styles = [];
                if (isNil(question) || !question.finished) {
                    styles.push("color: #cccccc;");
                } else {
                    if (question.correct) {
                        styles.push("color: #00bb00;");
                    } else {
                        styles.push("color: #9d261d;");
                    }
                }
                return styles.join(' ');
            },
        },
        mounted () {
            const ownQuestion = this.ownQuestion;
            if (ownQuestion) {
                this.fetchMdlQuestion({
                    questionid: this.ownQuestion.id
                }).then(mdlQuestion => {
                    this.mdlQuestion = mdlQuestion;
                })
            }
        },
        components: {LoadingIcon}
    }
</script>


<style lang="scss">
    .question-tile {
        min-height: 40px;
        padding: 10px;
        background-color: #ccc;
        border: 1px solid #999;
        border-radius: 10px;
        color: #333;
    }
</style>


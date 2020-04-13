<template lang="pug">
    router-link(:to="{name: 'player-question-play', params: {topicId: topic.id, matchId: match.id}}")
        vk-grid(matched, :class="{'_pointer': !isQuestionAnswered}").uk-flex-middle
            .uk-width-1-5
                v-icon(:name="leftIcon", :scale="2", :style="leftStyle")
            .uk-width-3-5
                .question-tile.uk-text-center.uk-text-middle
                    template(v-if="isQuestionAnswered")
                        span(v-if="mdlQuestion", v-html="mdlQuestion.questiontext")
                        loadingIcon(v-else)
                    span(v-else) {{ strings.game_tournament_match_lbl_question | stringParams(index + 1) }}
            .uk-width-1-5
                v-icon(:name="rightIcon", :scale="2", :style="rightStyle")
</template>

<script>
    import cloneDeep from 'lodash/cloneDeep';
    import first from 'lodash/first';
    import langMixins from '../../../../mixins/lang-mixins';
    import {mapGetters, mapState, mapActions} from 'vuex';
    import level from "../../../helper/level";
    import LoadingIcon from "../../../helper/loading-icon";

    export default {
        mixins: [langMixins],
        props: {
            index: Number,
            match: Object,
            topic: Object,
            mdlUserLeft: Number,
            mdlUserRight: Number,
            questions: Array,
            ownUserId: Number,
        },
        data () {
            return {
                mdlQuestion: null,
            }
        },
        computed: {
            ...mapState(['strings', 'game']),
            ...mapGetters(['getLevel']),
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
            level() {
                let level = cloneDeep(this.getLevel(this.topic.level));
                level.finished = this.isQuestionAnswered;
                level.seen = false;
                return level;
            },
            ownQuestion() {
                return this.getQuestionByUser(this.ownUserId);
            },
            isQuestionAnswered() {
                return this.ownQuestion !== null && this.ownQuestion.finished;
            },
        },
        methods: {
            ...mapActions({
                fetchMdlQuestion: 'player/fetchMdlQuestion',
            }),
            getQuestionByUser(mdlUserId) {
                const questions = this.questions.filter(q => (q.topic === this.topic.id && q.mdl_user === mdlUserId));
                if (questions.length > 0) {
                    return first(questions);
                }
                return null;
            },
            getIconByQuestion(question) {
                if (question === null || !question.finished) {
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
                if (question === null || !question.finished) {
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
        components: {LoadingIcon, level}
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


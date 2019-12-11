<template lang="pug">
    vk-grid(matched)
        .uk-width-1-5
            .uk-width-expand.question-left(:class="leftClass")
        .uk-width-3-5.uk-text-center
            level(:game="game", :level="level", @onSelectLevel="goToQuestion")
        .uk-width-1-5
            .uk-width-expand.question-right(:class="rightClass")
</template>

<script>
    import _ from 'lodash';
    import {mapGetters, mapState} from 'vuex';
    import level from "../../../helper/level";

    export default {
        props: {
            topic: Object,
            mdlUserLeft: Number,
            mdlUserRight: Number,
            questions: Array,
            ownUserId: Number,
        },
        computed: {
            ...mapState(['game']),
            ...mapGetters(['getLevel']),
            leftClass() {
                const question = this.getQuestionByTopicAndUser(this.topic.id, this.mdlUserLeft);
                return this.getClassByQuestion(question);
            },
            rightClass() {
                const question = this.getQuestionByTopicAndUser(this.topic.id, this.mdlUserRight);
                return this.getClassByQuestion(question);
            },
            level() {
                let level = _.cloneDeep(this.getLevel(this.topic.level));
                level.finished = this.isQuestionAnswered;
                level.seen = false;
                return level;
            },
            isQuestionAnswered() {
                const questions = _.filter(this.questions, q => (q.topic === this.topic.id && q.mdl_user === this.ownUserId));
                if (questions.length > 0) {
                    return !_.first(questions).finished;
                } else {
                    return true;
                }
            },
        },
        methods: {
            getQuestionByTopicAndUser(topicId, mdlUserId) {
                const questions = _.filter(this.questions, q => (q.topic === topicId && q.mdl_user === mdlUserId));
                if (questions.length > 0) {
                    return _.first(questions);
                }
                return null;
            },
            getClassByQuestion(question) {
                if (question === null || !question.finished) {
                    return "question-open";
                } else {
                    if (question.correct) {
                        return "question-correct";
                    } else {
                        return "question-incorrect";
                    }
                }
            },
            goToQuestion() {
                this.$router.push({name: 'player-question-play', params: {topicId: this.topic.id}});
            }
        },
        components: {level}
    }
</script>

<style lang="scss">
    .question-left {
        border-top-right-radius: 10px;
        border-bottom-right-radius: 10px;
    }

    .question-right {
        border-top-left-radius: 10px;
        border-bottom-left-radius: 10px;
    }

    .question-correct {
        background-color: #00bb00;
        border: 1px solid #00bb00;
    }

    .question-incorrect {
        background-color: #9d261d;
        border: 1px solid #9d261d;
    }

    .question-open {
        background-color: #ffffff;
        border: 1px solid #ccc;
    }
</style>

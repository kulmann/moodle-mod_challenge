<template lang="pug">
    vk-grid(matched)
        .uk-width-1-5(:class="leftClass")
        .uk-width-3-5.uk-text-center
            b {{ topic.level_name }}
        .uk-width-1-5(:class="rightClass")
</template>

<script>
    import _ from 'lodash';

    export default {
        props: {
            topic: Object,
            mdlUserLeft: Number,
            mdlUserRight: Number,
            questions: Array,
        },
        computed: {
            leftClass() {
                const question = this.getQuestionByTopicAndUser(this.topic.id, this.mdlUserLeft);
                return this.getClassByQuestion(question);
            },
            rightClass() {
                const question = this.getQuestionByTopicAndUser(this.topic.id, this.mdlUserRight);
                return this.getClassByQuestion(question);
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
        }
    }
</script>

<style lang="scss">
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

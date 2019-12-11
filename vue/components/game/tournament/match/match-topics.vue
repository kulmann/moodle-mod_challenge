<template lang="pug">
    .uk-card.uk-card-default
        .uk-card-body
            template(v-for="(topic, index) in topics")
                topicRow(:key="'topic-row-' + topic.id",
                    :topic="topic",
                    :mdlUserLeft="match.mdl_user_1",
                    :mdlUserRight="match.mdl_user_2",
                    :questions="getQuestionsByTopic(topic.id)",
                )
                .uk-width-expand.uk-text-center(v-if="isQuestionOpen(topic.id)")
                    button.btn.btn-primary(@click="goToQuestion(topic.id)") {{ strings.game_btn_answer }}
                .uk-heading-divider.uk-margin-small-bottom(v-if="!lastRow(index)")
</template>

<script>
    import _ from 'lodash';
    import {mapState} from 'vuex';
    import topicRow from "./topic-row";

    export default {
        props: {
            match: Object,
            topics: Array,
            questions: Array,
            ownUserId: Number,
        },
        computed: {
            ...mapState(['strings']),
        },
        methods: {
            lastRow(index) {
                return index === this.topics.length - 1;
            },
            getQuestionsByTopic(topicId) {
                return _.filter(this.questions, q => q.topic === topicId);
            },
            isQuestionOpen(topicId) {
                const questions = _.filter(this.questions, q => (q.topic === topicId && q.mdl_user === this.ownUserId));
                if (questions.length > 0) {
                    return !_.first(questions).finished;
                } else {
                    return true;
                }
            },
            goToQuestion(topicId) {
                this.$router.push({name: 'player-question-play', params: {topicId: topicId}})
            }
        },
        components: {topicRow}
    }
</script>

<style lang="scss">
    .topic-row-border {
        border-bottom: 1px solid #ccc;
    }
</style>

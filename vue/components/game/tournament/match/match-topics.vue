<template lang="pug">
    div
        vk-grid(matched).uk-text-middle
            .uk-width-2-5.uk-text-center
                span
                    userAvatar(:size="80", :user="user1")
                b {{ user1.firstname }} {{ user1.lastname }}
            .uk-width-1-5.uk-text-center.uk-flex-middle
                i {{ strings.game_tournament_match_versus }}
            .uk-width-2-5.uk-text-center
                span
                    userAvatar(:size="80", :user="user2")
                b {{ user2.firstname }} {{ user2.lastname }}
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
    import {mapGetters, mapState} from 'vuex';
    import topicRow from "./topic-row";
    import userAvatar from "../../../helper/user-avatar";

    export default {
        props: {
            match: Object,
            topics: Array,
            questions: Array,
            ownUserId: Number,
        },
        computed: {
            ...mapState(['strings']),
            ...mapGetters(['getMdlUser']),
            user1 () {
                return this.getMdlUser(this.match.mdl_user_1);
            },
            user2 () {
                return this.getMdlUser(this.match.mdl_user_2);
            },
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
        components: {userAvatar, topicRow}
    }
</script>

<style lang="scss">
    .topic-row-border {
        border-bottom: 1px solid #ccc;
    }
</style>

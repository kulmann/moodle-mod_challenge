<template lang="pug">
    div
        vk-grid(matched).uk-text-middle.uk-margin-bottom.uk-grid-small
            .uk-width-1-3.uk-text-center
                span
                    userAvatar(:size="80", :user="user1")
                b {{ user1.firstname }} {{ user1.lastname }}
            .uk-width-1-3.uk-text-center.uk-flex-middle.match-versus
                span(v-if="finished") {{ user1Score }} : {{ user2Score }}
                span(v-else) {{ user1Score }} : ?
            .uk-width-1-3.uk-text-center
                span
                    userAvatar(:size="80", :user="user2")
                b {{ user2.firstname }} {{ user2.lastname }}
        template(v-for="question in questionMocks")
            question-row(:key="`match-${match.id}-question-row-${question.number}`",
                :question="question",
                :match="match",
                :mdlUserLeft="match.mdl_user_1",
                :mdlUserRight="match.mdl_user_2",
                :attempts="attempts"
                :ownUserId="ownUserId"
            )
            .uk-heading-divider.uk-margin-small-bottom(v-if="!isLastRow(question)")
</template>

<script>
    import {mapGetters, mapState} from 'vuex';
    import first from 'lodash/first';
    import questionRow from "./match-question-row";
    import userAvatar from "../../helper/user-avatar";

    export default {
        props: {
            round: {
                type: Object,
                required: true
            },
            match: {
                type: Object,
                required: true
            },
            questions: {
                type: Array,
                required: true
            },
            attempts: {
                type: Array,
                required: true
            },
            ownUserId: {
                type: Number,
                required: true
            },
        },
        computed: {
            ...mapState(['strings']),
            ...mapGetters(['getMdlUser']),
            user1() {
                return this.getMdlUser(this.match.mdl_user_1);
            },
            user2() {
                return this.getMdlUser(this.match.mdl_user_2);
            },
            questionMocks() {
                const questions = [];
                for (let i = 0; i < this.round.questions; i++) {
                    const existingQuestion = first(this.questions.filter(question => question.matchid === this.match.id && question.number === (i + 1)));
                    if (existingQuestion) {
                        questions.push(existingQuestion);
                    } else {
                        questions.push({
                            number: (i + 1)
                        });
                    }
                }
                return questions;
            },
            finished() {
                return this.match.open === false;
            },
            user1Score() {
                return this.getScoreSum(this.match.mdl_user_1);
            },
            user2Score() {
                return this.getScoreSum(this.match.mdl_user_2);
            }
        },
        methods: {
            isLastRow(question) {
                return question.number === this.round.questions;
            },
            getScoreSum(userId) {
                return this.attempts.filter(attempt => attempt.mdl_user === userId && attempt.finished === true)
                    .map(attempt => attempt.score)
                    .reduce((a,b) => a + b, 0);
            }
        },
        components: {userAvatar, questionRow}
    }
</script>

<style lang="scss" scoped>
    .match-versus {
        font-size: 3em;
        font-weight: bold;
    }
    .question-row-border {
        border-bottom: 1px solid #ccc;
    }
</style>

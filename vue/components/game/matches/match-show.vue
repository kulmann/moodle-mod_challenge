<template lang="pug">
    div
        vk-grid(matched).uk-text-middle.uk-margin-bottom
            .uk-width-2-5.uk-text-center
                span
                    userAvatar(:size="80", :user="user1")
                b {{ user1.firstname }} {{ user1.lastname }}
            .uk-width-1-5.uk-text-center.uk-flex-middle.match-versus
                i {{ strings.game_match_versus }}
            .uk-width-2-5.uk-text-center
                span
                    userAvatar(:size="80", :user="user2")
                b {{ user2.firstname }} {{ user2.lastname }}
        template(v-for="question in questionMocks")
            question-row(:key="'question-row-' + question.number",
                :question="question",
                :match="match",
                :mdlUserLeft="match.mdl_user_1",
                :mdlUserRight="match.mdl_user_2",
                :questions="questions",
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
                    const existingQuestion = first(this.questions.filter(question => question.number === (i + 1)));
                    if (existingQuestion) {
                        questions.push(existingQuestion);
                    } else {
                        questions.push({
                            number: (i + 1)
                        });
                    }
                }
                return questions;
            }
        },
        methods: {
            isLastRow(question) {
                return question.number === this.round.questions;
            },
        },
        components: {userAvatar, questionRow}
    }
</script>

<style lang="scss" scoped>
    .match-versus {
        font-size: 3em;
        font-weight: bold;
        font-style: italic;
        text-transform: uppercase;
    }
    .question-row-border {
        border-bottom: 1px solid #ccc;
    }
</style>

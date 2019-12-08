<template lang="pug">
    .uk-alert(uk-alert, :class="alertClass").uk-float-left
        span.uk-margin-small-left.uk-margin-small-bottom.uk-float-right
            button.btn.uk-margin-remove._pointer(@click="onSubmit", :class="buttonClass")
                span &nbsp;{{ labelSubmit }}
                loadingIcon(v-if="submitClicked")
            a.uk-margin-small-left.uk-margin-remove-top.uk-margin-remove-bottom.uk-preserve-width._close-panel(@click="onCancel", :disabled="submitClicked")
                v-icon(name="times")
        v-icon(:name="icon").uk-margin-small-right
        span._word-wrapping(v-html="message")
</template>

<script>
    import loadingIcon from "./loading-icon";

    export default {
        props: {
            message: String,
            icon: {
                type: String,
                default: 'exclamation-circle',
                validator(value) {
                    return ['exclamation-circle', 'question-circle'].indexOf(value) !== -1;
                },
            },
            type: {
                type: String,
                default: 'danger',
                validator(value) {
                    return ['primary', 'success', 'warning', 'danger'].indexOf(value) !== -1;
                },
            },
            labelSubmit: {
                type: String,
                default: 'Confirm',
            },
        },
        data() {
            return {
                submitClicked: false,
            }
        },
        computed: {
            alertClass() {
                return 'uk-alert-' + this.type;
            },
            buttonClass() {
                return 'btn-' + this.type;
            },
        },
        methods: {
            onSubmit() {
                if (this.submitClicked) {
                    return;
                }
                this.submitClicked = true;
                this.$emit('onSubmit');
            },
            onCancel() {
                if (this.submitClicked) {
                    return;
                }
                this.$emit('onCancel');
            }
        },
        mounted() {
            this.submitClicked = false;
        },
        components: {loadingIcon}
    }
</script>

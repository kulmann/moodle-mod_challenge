<template lang="pug">
    .uk-alert.uk-alert-primary(uk-alert)
        p
            v-icon(v-if="icon", :name="icon").uk-margin-small-right
            span {{ message }}
                loadingIcon
</template>

<script>
    import loadingIcon from "./loading-icon";
    export default {
        props: {
            icon: String,
            message: String
        },
        components: {loadingIcon}
    }
</script>

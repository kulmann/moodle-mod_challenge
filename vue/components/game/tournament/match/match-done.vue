<template lang="pug">
    .uk-card.uk-card-default
        .uk-card-body
            matchHeader(:match="match")
</template>

<script>
    import matchHeader from "./match-header";

    export default {
        props: {
            match: Object,
        },
        components: {matchHeader}
    }
</script>

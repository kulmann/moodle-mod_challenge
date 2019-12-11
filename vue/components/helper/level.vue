<template lang="pug">
    .level(@click="selectLevel()", :style="levelStyles", :class="{'_pointer': !levelFinished}")
        .uk-flex.uk-flex-middle.level(:style="textStyles").uk-box-shadow-hover-large
            .uk-flex.uk-width-expand
                .uk-width-expand.level-content.uk-text-center
                    template(v-if="level.seen")
                        b {{ level.name }}
                        br
                        span.done(v-if="level.score === 1") {{ strings.game_progress_point | stringParams(1) }}
                        span.done(v-else) {{ strings.game_progress_points | stringParams(level.score) }}
                    b.open(v-else) {{ level.name }}
</template>

<script>
    import mixins from '../../mixins';
    import {mapState} from 'vuex';

    export default {
        mixins: [mixins],
        props: {
            level: Object,
            game: Object,
        },
        computed: {
            ...mapState(['strings']),
            textStyles() {
                let alpha = ((100 - this.game.level_tile_alpha) / 100.0);
                let styles = [
                    'background-color: rgba(0,0,0,' + alpha + ');',
                    'color: #fff',
                ];
                return styles.join(' ');
            },
            levelStyles() {
                let styles = [
                    'min-height: ' + this.level.tile_height_px + 'px;',
                    'max-height: ' + this.level.tile_height_px + 'px;',
                ];
                // bg color
                if (this.level.bgcolor) {
                    styles.push('background-color: ' + this.level.bgcolor + ';');
                }
                // bg image
                if (this.level.imageurl) {
                    styles.push('background-image: url(' + this.level.imageurl + ');');
                    styles.push('background-size: cover;');
                    styles.push('background-position: center;');
                }
                return styles.join(' ');
            },
            levelFinished() {
                return this.level.finished;
            }
        },
        methods: {
            selectLevel() {
                if (!this.levelFinished) {
                    this.$emit('onSelectLevel', this.level.id);
                }
            },
        }
    }
</script>

<style lang="scss" scoped>
    .level {
        height: 100%;
        border-radius: 10px;
    }

    .level-content {
        margin-top: 10px;
        margin-bottom: 10px;
        text-align: center;
    }

    .open {
        font-size: 1.1em;
    }
</style>

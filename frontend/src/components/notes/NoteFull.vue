<template>
    <div class="o-note-full">
        <!-- <div
            v-html="noteFormatted"
        >
        </div>
 -->
        <TipTapDisplay
            :content="note.tiptap"
        >
        </TipTapDisplay>
    </div>
</template>

<script>

import TipTapDisplay from '@/tiptap/TipTapDisplay.vue';

export default {
    name: 'NoteFull',
    components: {
        TipTapDisplay,
    },
    mixins: [
    ],
    props: {
        note: {
            type: Object,
            required: true,
        },
    },
    data() {
        return {
        };
    },
    computed: {
    },
    methods: {

    },
    created() {

    },
};
</script>

<style scoped>

/*.o-note-full {

} */

</style>

<template>
    <div
        class="o-note-mini feature__item--mini"
        @click="$emit('selectNote', note)"
    >
        <div class="text-xssm font-semibold text-cm-800">
            {{ name }}
        </div>

        <div class="text-xs text-cm-600">
            {{ preview }}
        </div>
    </div>
</template>

<script>

import interactsWithNoteItem from '@/vue-mixins/notes/interactsWithNoteItem.js';

export default {
    name: 'NoteMini',
    components: {

    },
    mixins: [
        interactsWithNoteItem,
    ],
    props: {

    },
    emits: [
        'selectNote',
    ],
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

/*.o-note-mini {

} */

</style>

<template>
    <div class="c-action-buttons flex">
        <EditButton
            class="mr-1"
            :class="{ 'opacity-0 pointer-events-none': hideEdit }"
            :showText="showText"
            :buttonComponent="buttonComponent"
            :size="size"
            @click="$emit('edit')"
        >
        </EditButton>

        <DeleteButton
            :class="{ 'opacity-0 pointer-events-none': hideDelete }"
            :showText="showText"
            :buttonComponent="buttonComponent"
            :size="size"
            @click="$emit('delete')"
        >
        </DeleteButton>
    </div>
</template>

<script>

import DeleteButton from './DeleteButton.vue';
import EditButton from './EditButton.vue';

export default {
    name: 'ActionButtons',
    components: {
        DeleteButton,
        EditButton,
    },
    mixins: [
    ],
    props: {
        showText: Boolean,
        buttonComponent: {
            type: String,
            default: 'IconActionButton',
            validator(val) {
                return ['IconActionButton', 'IconButton'].includes(val);
            },
        },
        hideDelete: Boolean,
        hideEdit: Boolean,
        size: {
            type: String,
            default: 'base',
        },
    },
    emits: [
        'delete',
        'edit',
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

/*
.c-action-buttons {

}
 */

</style>

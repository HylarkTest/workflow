<template>
    <GroupForge
        class="o-group-item-edit"
        :editMode="true"
        v-bind="$attrs"
        :hasFocusOnOpen="true"
        @submitGroupItem="$emit('saveChanges', $event)"
    >
    </GroupForge>
</template>

<script>

import GroupForge from './GroupForge.vue';

export default {
    name: 'GroupItemEdit',
    components: {
        GroupForge,
    },
    mixins: [
    ],
    props: {
    },
    emits: [
        'saveChanges',
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

/*.o-group-item-edit {

} */

</style>

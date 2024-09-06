<template>
    <div class="c-group-list">
        <GroupFoundation
            v-for="group in groups"
            :key="group.id"
            :ref="setRef(group)"
            class="customize__container p-6 mb-6 last:mb-0"
            :group="group"
            v-bind="$attrs"
        >
        </GroupFoundation>
    </div>
</template>

<script>

import GroupFoundation from './GroupFoundation.vue';

export default {
    name: 'GroupList',
    components: {
        GroupFoundation,
    },
    mixins: [
    ],
    props: {
        groups: {
            type: Array,
            required: true,
        },
    },
    emits: [
    ],
    data() {
        return {
            groupRefs: {},
        };
    },
    computed: {
    },
    methods: {
        setRef(group) {
            return (el) => {
                this.groupRefs[group.id] = el;
            };
        },
        focusOnLastAdded(newGroups, oldGroups) {
            const lastAddedGroup = _.differenceBy(newGroups, oldGroups, 'id')[0];
            // Focus in on the newly added group. Please improve this.
            this.$nextTick(() => {
                this.groupRefs[lastAddedGroup.id]
                    .$refs.groupMain
                    .$refs.groupForge.focusNew();
            });
        },
    },
    created() {

    },
};
</script>

<style scoped>

/*.c-group-list {

} */

</style>

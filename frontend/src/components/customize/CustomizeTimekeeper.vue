<template>
    <CustomizeFoundation class="o-customize-timekeeper">

        <template #header>
            <i
                v-if="symbol"
                class="far mr-2"
                :class="symbol"
            >
            </i>
            Timekeeper
        </template>

        <template #besideHeader>
            <button
                class="button-primary button--sm"
                type="button"
                @click="openModal"
            >
                <i
                    class="fal fa-plus-circle mr-1"
                >
                </i>
                Add deadline group
            </button>
        </template>

        <GroupList
            v-if="groups && groups.length"
            ref="list"
            :groups="groups"
            groupType="TIMEKEEPER"
            itemDisplayComponent="PhaseDisplay"
            :hasUsesButton="true"
            :allPages="allPages"
        >
        </GroupList>

        <Modal
            v-if="isModalOpen"
            containerClass="p-4 w-600p"
            @closeModal="closeModal"
        >
            <GroupNew
                groupType="TIMEKEEPER"
                @saveNewGroup="saveNewGroup"
            >
            </GroupNew>
        </Modal>
    </CustomizeFoundation>
</template>

<script>

import interactsWithGroupCustomizations from '@/vue-mixins/customizations/interactsWithGroupCustomizations.js';

export default {
    name: 'CustomizeTimekeeper',
    components: {
    },
    mixins: [
        interactsWithGroupCustomizations,
    ],
    props: {
        allPages: {
            type: Array,
            required: true,
        },
    },
    data() {
        return {
            groups: [],
            groupType: 'TIMEKEEPER',
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

/*.o-customize-timekeeper {

} */

</style>

<template>
    <div
        v-if="!$apollo.loading"
        class="o-entity-content max-h-full"
    >
        <div
            v-if="page.item"
            class="bg-cm-00 rounded-xl h-full"
        >
            <FullView
                :item="page.item"
                :page="page"
                context="ENTITY"
            >
            </FullView>
        </div>

        <div
            v-else
            class="bg-cm-00 rounded-xl p-8"
        >
            <div class="border-b border-cm-400 border-dashed pb-8 mb-8 relative">
                <h2
                    class="text-xl font-semibold text-center text-primary-600 mb-2"
                >
                    Display an existing "{{ mapping.name }}" record on this page
                </h2>

                <div class="centered">
                    <EntitiesPicker
                        class="w-1/2"
                        :modelValue="null"
                        :entityVal="null"
                        bgColor="gray"
                        placeholder="Find record"
                        :mappingId="mapping.id"
                        :spaceId="mapping.space.id"
                        @update:modelValue="setItem"
                    >
                    </EntitiesPicker>
                </div>

                <div class="absolute -bottom-3 text-center w-full">
                    <div class="o-entity-content__or">
                        Or
                    </div>
                </div>
            </div>
            <EntityNew
                :page="page"
                :mapping="mapping"
                :fullForm="true"
                @saved="setItem"
            >
            </EntityNew>
        </div>
    </div>
</template>

<script>
import EntitiesPicker from '@/components/pickers/EntitiesPicker.vue';

import providesEntityConnectionsInfo from '@/vue-mixins/providesEntityConnectionsInfo.js';

import { updateMappingPage } from '@/core/repositories/pageRepository.js';

export default {
    name: 'EntityContent',
    components: {
        EntitiesPicker,
    },
    mixins: [
        providesEntityConnectionsInfo,
    ],
    props: {
        page: {
            type: Object,
            required: true,
        },
        mapping: {
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
        setItem(item) {
            updateMappingPage(this.$apolloForm({ id: this.page.id, entityId: item.id }), this.page);
        },
    },
    created() {

    },
};
</script>

<style scoped>

.o-entity-content {
    &__or {
        @apply
            bg-secondary-100
            font-bold
            inline-flex
            px-2
            py-1
            rounded-full
            text-secondary-600
            text-sm
        ;
    }
}

</style>

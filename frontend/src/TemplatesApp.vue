<template>
    <div class="o-templates-app">
        <div class="flex flex-wrap gap-4 justify-center">
            <a
                v-for="use in uses"
                :key="use.val"
                :href="useLink(use)"
            >
                <UseItem
                    class="o-templates-app__item"
                    :use="use"
                    :base="{ baseType: 'PERSONAL' }"
                >
                </UseItem>
            </a>
        </div>
    </div>
</template>

<script>
import UseItem from '@/components/access/UseItem.vue';

import uses from '@/core/mappings/templates/uses.js';

export default {
    components: {
        UseItem,
    },
    props: {},
    data() {
        return {
        };
    },
    methods: {
        useLink(use) {
            return `${import.meta.env.VITE_URL}/signup?uses=${use.val}`;
        },
    },
    created() {
        this.uses = uses;
    },
};
</script>

<style scoped>
.o-templates-app {
    &__item {
        height: 100%;
    }
}
</style>

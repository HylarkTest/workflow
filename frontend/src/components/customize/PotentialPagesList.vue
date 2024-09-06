<template>
    <div class="o-potential-pages-list">
        <PageCardPotential
            v-for="page in potentialPages"
            :key="page.id"
            :page="page"
            class="o-potential-pages-list__card"
            @selectPage="openPageCreation(page, 'STRUCTURE')"
        >
        </PageCardPotential>

        <ButtonEl
            class="o-potential-pages-list__card o-potential-pages-list__general"
            @click="openPageCreation(null, 'PAGES')"
        >
            <span class="text-center mb-2">
                {{ $t('customizations.pages.potential.prompt') }}
            </span>

            <AddCircle
                buttonType="primary"
            >
            </AddCircle>
        </ButtonEl>
    </div>
</template>

<script>

import PageCardPotential from './PageCardPotential.vue';
import AddCircle from '@/components/buttons/AddCircle.vue';

export default {
    name: 'PotentialPagesList',
    components: {
        AddCircle,
        PageCardPotential,
    },
    mixins: [
    ],
    props: {
        potentialPages: {
            type: Array,
            required: true,
        },
    },
    emits: [
        'openPageCreation',
    ],
    data() {
        return {

        };
    },
    computed: {

    },
    methods: {
        openPageCreation(page = null, initialStep = '') {
            this.$emit('openPageCreation', { page, initialStep });
        },
    },
    created() {
    },
};
</script>

<style scoped>
.o-potential-pages-list {
    @apply
        flex
        flex-wrap
        gap-4
        justify-center
        p-6
    ;

    &__card {
        height: 115px;
        transition: 0.2s ease-in-out;
        width: 175px;

        &:hover {
            transform: scale(1.03);
        }
    }

    &__general {
        @apply
            bg-cm-00
            border-2
            border-dashed
            border-primary-300
            flex
            flex-col
            font-semibold
            items-center
            justify-center
            rounded-xl
            text-smbase
        ;
    }
}
</style>

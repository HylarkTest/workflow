<template>
    <div class="c-person-finder">

        <EntitiesPicker
            v-model:entityVal="personSearch"
            :placeholder="placeholder"
            type="PERSON"
            hasEmails
            v-bind="$attrs"
        >
        </EntitiesPicker>

        <div
            class="flex text-xs mt-1"
        >
            <div
                v-for="person in selectedPeople"
                :key="person.hylarkId || person.tempId"
                class="c-person-finder__person"
            >
                <div>
                    {{ person.name || person.emailAddress }}
                </div>

                <button type="button">
                    <i
                        class="far fa-times ml-2"
                    >
                    </i>
                </button>
            </div>
        </div>
    </div>
</template>

<script>

import EntitiesPicker from '@/components/pickers/EntitiesPicker.vue';

export default {
    name: 'PersonFinder',
    components: {
        EntitiesPicker,
    },
    mixins: [
    ],
    props: {
        findBasedOn: {
            type: Array,
            default() {
                return ['NAME', 'EMAIL'];
            },
        },
        placeholder: {
            type: String,
            default: 'Find people',
        },
        selectedPeople: {
            type: Array,
            default: () => ([]),
        },
    },
    data() {
        return {
            searchParameter: '',
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

.c-person-finder {
    &__person {
        @apply
            bg-cm-200
            flex
            items-center
            m-1
            px-3
            py-0.5
            rounded-full
        ;
    }
}

</style>

<template>
    <div class="c-feature-quick-form bg-primary-100 p-4 rounded-xl relative">
        <div
            class="c-feature-quick-form__banner"
        >
            {{ $t(stringPath) }}
            <span
                class="font-bold"
                :style="{ color: listColor }"
            >
                {{ listName }}
            </span>
        </div>

        <slot>
        </slot>
    </div>
</template>

<script>

export default {
    name: 'FeatureQuickForm',
    components: {

    },
    mixins: [
    ],
    props: {
        displayedList: {
            type: Object,
            required: true,
        },
        featureType: {
            type: String,
            required: true,
            validator(val) {
                return [
                    'TODOS',
                    'NOTES',
                ].includes(val);
            },
        },
    },
    data() {
        return {

        };
    },
    computed: {
        listName() {
            return this.displayedList.name;
        },
        listColor() {
            return this.displayedList.color && this.$root.extraColorDisplay(this.displayedList.color);
        },
        featureTypeFormatted() {
            return _.camelCase(this.featureType);
        },
        stringPath() {
            return `features.${this.featureTypeFormatted}.quickAdd`;
        },
    },
    methods: {

    },
    created() {

    },
};
</script>

<style scoped>

.c-feature-quick-form {
    &__banner {
        @apply
            font-medium
            mb-1
            text-xs
        ;
    }
}

</style>

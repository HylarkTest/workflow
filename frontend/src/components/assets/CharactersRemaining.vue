<template>
    <div
        class="c-characters-remaining text-xxs"
        :class="[positioningClasses, charactersClass]"
    >
        {{ characterString }}
    </div>
</template>

<script>

export default {
    name: 'CharactersRemaining',
    components: {

    },
    mixins: [
    ],
    props: {
        positioningClasses: {
            type: String,
            default: '',
        },
        length: {
            type: Number,
            default: 0,
        },
        maxLength: {
            type: Number,
            default: 2000,
        },
    },
    data() {
        return {

        };
    },
    computed: {
        remainingCharacters() {
            return this.maxLength - this.length;
        },
        characterString() {
            return `${this.length} / ${this.maxLength}`;
        },
        charactersClass() {
            return [
                this.remainingCharactersClass,
            ];
        },
        hundredRemaining() {
            return this.remainingCharacters < 100;
        },
        remainingCharactersClass() {
            return this.hundredRemaining ? 'text-rose-600 font-bold' : 'text-cm-600';
        },
    },
    methods: {

    },
    created() {

    },
};
</script>

<style scoped>

/*.c-characters-remaining {

} */

</style>

<template>
    <div class="c-dropdown-label text-sm">
        <i
            v-if="label.icon"
            :class="[label.icon, labelClass]"
        >
        </i>
        <label
            v-if="label.text"
            class="ml-1"
            :class="textClass"
        >
            {{ label.text }}<template v-if="!label.hideColon">:</template>
        </label>
    </div>
</template>

<script>

export default {
    name: 'DropdownLabel',
    components: {

    },
    mixins: [
    ],
    props: {
        label: {
            type: Object,
            required: true,
        },
    },
    data() {
        return {

        };
    },
    computed: {
        labelClass() {
            return _.has(this.label, 'iconClass') ? this.label.iconClass : 'text-primary-600';
        },
        textClass() {
            if (_.has(this.label, 'textClass')) {
                return this.label.textClass;
            }
            if (this.label.useActiveColor) {
                return 'text-primary-600';
            }
            return 'text-cm-500';
        },
    },
    methods: {

    },
    created() {

    },
};
</script>

<style scoped>

/*
.c-dropdown-label {

}
 */

</style>

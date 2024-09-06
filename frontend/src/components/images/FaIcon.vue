<!-- Can use with by simply passing "class" from parent
or by using "icon" and "prefix" props if needing to deal with custom icons
 -->
<template>
    <i
        :class="symbol"
    >
    </i>
</template>

<script>

export default {
    name: 'FaIcon',
    components: {

    },
    mixins: [
    ],
    props: {
        icon: {
            type: String,
            default: '',
        },
        prefix: {
            type: String,
            default: '',
        },
    },
    data() {
        return {

        };
    },
    computed: {
        symbol() {
            if (this.icon.includes('fak')) {
                return this.icon;
            }
            return `${this.prefix} ${this.icon}`;
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
.c-fa-icon {

}
*/

</style>

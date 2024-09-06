<template>
    <DropdownBox
        class="c-role-select w-32"
        :options="roleArr"
        :displayRule="rolesDisplay"
        placeholder="Role"
        v-bind="$attrs"
    >

    </DropdownBox>
</template>

<script>

const basic = [
    'MEMBER',
    'ADMIN',
];

const all = [
    'MEMBER',
    'ADMIN',
    'OWNER',
];

export default {
    name: 'RoleSelect',
    components: {

    },
    mixins: [
    ],
    props: {
        roleArrName: {
            type: String,
            default: 'BASIC',
            validator: (val) => ['BASIC', 'ALL'].includes(val),
        },
    },
    data() {
        return {

        };
    },
    computed: {
        roleArrNameFormatted() {
            return _.camelCase(this.roleArrName);
        },
        roleArr() {
            return this[this.roleArrNameFormatted];
        },
    },
    methods: {

    },
    created() {
        this.basic = basic;
        this.all = all;
        this.rolesDisplay = (option) => this.$t(`common.roles.${_.toLower(option)}`);
    },
};
</script>

<style scoped>

/*.c-role-select {

} */

</style>

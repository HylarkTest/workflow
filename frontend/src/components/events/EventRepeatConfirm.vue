<template>
    <OptionsModal
        class="o-event-repeat-confirm"
        :buttons="buttons"
        :mainInfo="mainInfo"
        v-bind="$attrs"
    >

    </OptionsModal>
</template>

<script>

import OptionsModal from '@/components/assets/OptionsModal.vue';

export default {
    name: 'EventRepeatConfirm',
    components: {
        OptionsModal,
    },
    mixins: [
    ],
    props: {
        action: {
            type: String,
            default: 'CHANGE',
        },
        external: Boolean,
    },
    data() {
        return {

        };
    },
    computed: {
        mainInfo() {
            let subtitle;
            if (this.action === 'CHANGE') {
                subtitle = this.external
                    ? 'calendar.repeating.whatExternalChanging'
                    : 'calendar.repeating.whatChanging';
            } else {
                subtitle = this.external
                    ? 'calendar.repeating.whatExternalDeleting'
                    : 'calendar.repeating.whatDeleting';
            }
            return {
                title: this.action === 'CHANGE' ? 'calendar.repeating.changing' : 'calendar.repeating.deleting',
                subtitle,
                icon: 'fa-calendar-minus',
            };
        },
        buttons() {
            return [
                {
                    val: 'THIS_EVENT',
                    textPath: 'calendar.repeating.thisEvent',
                },
                {
                    val: 'ALL_FUTURE',
                    textPath: this.external
                        ? 'calendar.repeating.allEvents'
                        : 'calendar.repeating.allFutureEvents',

                },
            ];
        },
    },
    methods: {

    },
    created() {
    },
};
</script>

<style scoped>

/*.o-event-repeat-confirm {

} */

</style>

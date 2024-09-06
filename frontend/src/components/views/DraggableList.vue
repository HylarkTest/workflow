<template>
    <div class="c-draggable-list">
        <div
            v-for="(item, index) in items"
            :key="getItemKey(item, index)"
            :class="itemClass"
        >
            <slot
                name="item"
                :item="item"
            >
            </slot>
        </div>
    </div>
</template>

<script>

export default {
    name: 'DraggableList',
    components: {

    },
    mixins: [
    ],
    props: {
        items: {
            type: Array,
            required: true,
        },
        itemClass: {
            type: String,
            default: '',
        },
        itemKey: {
            type: String,
            default: null,
        },
    },
    data() {
        return {
        };
    },
    computed: {

    },
    methods: {
        getItemKey(item, index) {
            if (this.itemKey) {
                return item[this.itemKey];
            }
            return index;
        },
    },
    created() {

    },
};
</script>

<style scoped>

/*
.c-draggable-list {

}
*/

</style>

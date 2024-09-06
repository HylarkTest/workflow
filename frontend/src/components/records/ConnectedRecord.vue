<template>
    <component
        :is="recordEl"
        class="c-connected-record rounded-lg min-w-0 align-stretch"
        :class="[bgClass, deactivatedClass, paddingClasses]"
        @click.exact="openFull"
        @click.alt.prevent="goToPage"
    >
        <ImageName
            :name="name"
            :image="image"
            :size="imageSize"
            :icon="icon"
            :hideFullName="isMinimized"
            :colorName="colorName"
        >
        </ImageName>

        <button
            v-if="showClear && !item.doNotRemove"
            class="centered"
            type="button"
            @click.stop="$emit('removeItem', item)"
        >
            <ClearButton
                positioningClass="ml-2"
            >
            </ClearButton>
        </button>

        <Modal
            v-if="isModalOpen"
            containerClass="w-10/12"
            :containerStyle="{ height: '80vh' }"
            @closeModal="closeModal"
        >
            <FullView
                :item="item"
                :page="page"
                @closeModal="closeModal"
            >
            </FullView>
        </Modal>
    </component>
</template>

<script>
import ImageName from '@/components/images/ImageName.vue';
import ClearButton from '@/components/buttons/ClearButton.vue';

import interactsWithModal from '@/vue-mixins/interactsWithModal.js';

const openOptions = [
    {
        val: 'MODAL',
        action: 'openModal',
        icon: 'fa-expand-wide',
    },
    {
        val: 'TAB',
        action: 'openTab',
        icon: 'fa-browsers',
    },
];

export default {
    name: 'ConnectedRecord',
    components: {
        ImageName,
        ClearButton,
    },
    mixins: [
        interactsWithModal,
    ],
    props: {
        item: {
            type: Object,
            required: true,
        },
        bgColor: {
            type: String,
            default: 'gray',
        },
        showClear: Boolean,
        deactivated: Boolean,
        isMinimized: Boolean,
        imageSize: {
            type: String,
            default: 'md',
            validator(val) {
                return ['lg', 'md', 'sm', 'full'].includes(val);
            },
        },
        stopModalClicks: Boolean,
    },
    emits: [
        'removeItem',
    ],
    data() {
        return {

        };
    },
    computed: {
        recordEl() {
            return this.page ? 'ButtonEl' : 'div';
        },
        typeLabel() {
            return false;
        },
        name() {
            return this.item.name;
        },
        image() {
            return this.item.image?.url;
        },
        icon() {
            return this.item.icon;
        },
        itemRoute() {
            return {
                name: 'entityPage',
                params: { itemId: this.item.id, pageId: this.page.id },
            };
        },
        mapping() {
            return this.item.mapping;
        },
        page() {
            return (this.item.pages && this.item.pages[0])
                || (this.mapping?.pages && this.mapping.pages[0]);
        },
        deactivatedClass() {
            return { unclickable: this.deactivated };
        },
        paddingClasses() {
            return { 'py-0.5 px-2': !this.isMinimized };
        },
        bgClass() {
            return `c-connected-record__bg--${this.bgColor}`;
        },
        colorName() {
            return 'sky';
        },
        allowsClicks() {
            return this.page && !this.stopModalClicks && !this.deactivated;
        },
    },
    methods: {
        openFull() {
            if (this.allowsClicks) {
                this.openModal();
            }
        },
        goToPage() {
            if (this.allowsClicks) {
                this.$router.push(this.itemRoute);
            }
        },
    },
    created() {
        this.openOptions = openOptions;
    },
};
</script>

<style scoped>

.c-connected-record {
    @apply
        inline-flex
    ;

    &__bg {
        &--white {
            @apply
                bg-cm-00
            ;
        }

        &--gray {
            @apply
                bg-cm-100
            ;
        }
    }
}

</style>

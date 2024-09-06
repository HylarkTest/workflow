<template>
    <div class="o-registration-spaces-use">
        <UseMini
            :use="use"
            :base="base"
            :showName="true"
        >
            <div
                ref="blurParent"
                v-blur="closeDropdown"
                class="relative pr-2"
            >
                <button
                    ref="button"
                    type="button"
                    class="text-primary-600 text-2xl leading-none ml-2"
                    @click="toggleDropdown"
                >
                    <i
                        class="fal fa-ellipsis-v"
                    >
                    </i>
                </button>
                <PopupBasic
                    v-if="dropdownOpen"
                    widthProp="10rem"
                    :activator="$refs.button"
                    :blurParent="$refs.blurParent"
                    alignCenter
                >
                    <div class="leading-tight text-sm">
                        <button
                            type="button"
                            class="o-registration-spaces-use__action"
                            @click="deleteUse"
                        >
                            Delete
                        </button>
                        <button
                            v-for="otherSpace in otherSpaces"
                            :key="otherSpace"
                            type="button"
                            class="o-registration-spaces-use__action"
                            @click="duplicateUse(otherSpace)"
                        >
                            Duplicate to <span class="font-semibold">{{ otherSpace.name }}</span>
                        </button>
                    </div>
                </PopupBasic>
            </div>
        </UseMini>
    </div>
</template>

<script>

import UseMini from '@/components/access/UseMini.vue';

export default {
    name: 'RegistrationSpacesUse',
    components: {
        UseMini,

    },
    mixins: [
    ],
    props: {
        use: {
            type: Object,
            required: true,
        },
        space: {
            type: Object,
            required: true,
        },
        base: {
            type: Object,
            required: true,
        },
    },
    emits: [
        'deleteUse',
        'duplicateUse',
    ],
    data() {
        return {
            dropdownOpen: false,
        };
    },
    computed: {
        otherSpaces() {
            return this.base.spaces.filter((item) => {
                const foundUse = _.find(item.uses, { val: this.use.val });
                return item.id !== this.space.id && !foundUse;
            });
        },
    },
    methods: {
        toggleDropdown() {
            this.dropdownOpen = !this.dropdownOpen;
        },
        closeDropdown() {
            this.dropdownOpen = false;
        },
        deleteUse() {
            this.closeDropdown();
            this.$emit('deleteUse', { use: this.use, space: this.space });
        },
        duplicateUse(target) {
            this.closeDropdown();
            this.$emit('duplicateUse', { use: this.use, target });
        },
    },
    created() {

    },
};
</script>

<style>

.o-registration-spaces-use {
    &__action {
        @apply
            p-2
            w-full
            z-over
        ;

        &:hover {
            @apply
                bg-gray-100
            ;
        }
    }
}

</style>

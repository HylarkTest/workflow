<template>
    <div
        class="o-nav-folder"
        :class="{ 'mb-3': isOpen }"
    >
        <ButtonEl
            v-if="folderId"
            class="o-nav-folder__line"
            :class="isClosed ? '' : 'font-semibold'"
            @click="toggleState"
            @keyup.enter="toggleState"
            @keyup.space="toggleState"
        >
            <div class="min-w-0 flex mr-1">
                <i class="far fa-folder mt-1 mr-2 text-cm-300">
                </i>

                <span class="min-w-0 break-words">
                    {{ folderLang }}
                </span>
            </div>

            <div
                class="o-nav-folder__toggle centered shrink-0"
                :class="{ 'o-nav-folder__toggle--open': isOpen }"
            >
                <i
                    class="far"
                    :class="folderAngle"
                >
                </i>
            </div>
        </ButtonEl>

        <div
            v-if="isOpen"
            class="o-nav-folder__container"
            :class="{ 'o-nav-folder__container--border': folderId }"
        >
            <slot>
            </slot>
        </div>

        <div
            v-if="!folderId && !isSingle"
            class="h-divider my-2"
        >

        </div>
    </div>
</template>

<script>

import { arrRemove } from '@/core/utils.js';

export default {
    name: 'NavFolder',
    components: {

    },
    mixins: [
    ],
    props: {
        folder: {
            type: Object,
            required: true,
        },
        closedFolders: {
            type: Array,
            required: true,
        },
        isSingle: Boolean,
    },
    emits: [
        'update:closedFolders',
    ],
    data() {
        return {

        };
    },
    computed: {
        isOpen() {
            return !this.isClosed;
        },
        isClosed() {
            return this.closedFolders.includes(this.folderId);
        },
        folderId() {
            return this.folder.folder;
        },
        folderLang() {
            return this.folderId.slice(0, -1);
        },
        folderAngle() {
            const direction = this.isClosed ? 'down' : 'up';
            return `fa-angle-${direction}`;
        },
    },
    methods: {
        toggleState() {
            let newClosedFolders = _.clone(this.closedFolders);
            if (this.isClosed) {
                newClosedFolders = arrRemove(newClosedFolders, this.folderId);
            } else {
                newClosedFolders.push(this.folderId);
            }
            this.$emit('update:closedFolders', newClosedFolders);
        },
    },
    created() {

    },
};
</script>

<style scoped>

.o-nav-folder {
    &__line {
        @apply
            flex
            justify-between
            text-cm-800
        ;

        &:hover {
            .o-nav-folder__toggle:not(.o-nav-folder__toggle--open) {
                @apply
                    bg-primary-100
                    text-primary-600
                ;
            }
        }
    }

    &__toggle {
        height:  20px;
        transition: 0.2s ease-in-out;
        width:  20px;

        @apply
            rounded-md
        ;

        &--open {
            @apply
                bg-secondary-100
                text-secondary-600
            ;
        }
    }

    &__container {
        /*@apply
        ;*/

        &--border {
            @apply
                border-cm-100
                border-l
                border-solid
                ml-1
                pl-1
            ;
        }
    }
}

</style>

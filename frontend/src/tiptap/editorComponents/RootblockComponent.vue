<!-- RootBlockComponent will eventually be used to allow content blocks to be draggable.
This is deactivated for the MVP, however draggable requires a unique "root block" that all content is nested inside,
so the "root block" foundation is here for when the feature is added to avoid another conversion of all data. -->

<template>
    <NodeViewWrapper
        is="div"
        class="c-root-block-component group"
        :class="rootblockClasses"
    >
        <i
            v-if="hasBulletLeft"
            :class="bulletClasses"
        >
        </i>
        <NodeViewContent class="content w-fit flex-grow" />
        <i
            v-if="hasBulletRight"
            :class="bulletClasses"
        >
        </i>
    </NodeViewWrapper>
</template>

<script>
import {
    NodeViewContent,
    NodeViewWrapper,
} from '@tiptap/vue-3';

export default {
    name: 'RootblockComponent',
    components: {
        NodeViewWrapper,
        NodeViewContent,
    },
    props: {
        node: {
            type: Object,
            required: true,
        },
    },
    computed: {
        nodeAttrs() {
            return this.node.attrs;
        },
        alignment() {
            return this.nodeAttrs.alignment;
        },
        indent() {
            return this.nodeAttrs.indent;
        },
        hasBullet() {
            return this.nodeAttrs.hasBullet;
        },
        hasBulletRight() {
            return this.hasBullet && this.alignment === 'right';
        },
        hasBulletLeft() {
            return this.hasBullet && !this.hasBulletRight;
        },
        rootblockClasses() {
            return [
                `tiptap-align-${this.alignment}`,
                `tiptap-indent-${this.indent}`,
            ];
        },
        bulletIcon() {
            if (this.hasBullet) {
                return this.indent % 2 ? 'fa-solid fa-square-small' : 'fal fa-circle-small';
            }
            return null;
        },
        bulletClasses() {
            return [
                'tiptap-bullet',
                this.hasBulletRight ? 'tiptap-bullet__right' : 'tiptap-bullet__left',
                this.bulletIcon,
            ];
        },
    },
};
</script>

<style scoped>
.c-root-block-component {
    white-space: pre-wrap;
    @apply
        flex
        mb-4
    ;
}

.tiptap-bullet {
    @apply
        flex
        items-center
        justify-center
        text-sm
    ;

    &__left {
        @apply
            mr-2
        ;
    }

    &__right {
        @apply
            ml-2
        ;
    }
}

.tiptap-align-left {
    @apply
        text-left
    ;
}

.tiptap-align-center {
    @apply
        text-center
    ;
}

.tiptap-align-right {
    @apply
        text-right
    ;
}

.tiptap-indent-1 {
    &.tiptap-align-left {
        margin-left: 3rem;
    }
    &.tiptap-align-right {
        margin-right: 3rem;
    }
}
.tiptap-indent-2 {
    &.tiptap-align-left {
        margin-left: 6rem;
    }
    &.tiptap-align-right {
        margin-right: 6rem;
    }
}
.tiptap-indent-3 {
    &.tiptap-align-left {
        margin-left: 9rem;
    }
    &.tiptap-align-right {
        margin-right: 9rem;
    }
}
.tiptap-indent-4 {
    &.tiptap-align-left {
        margin-left: 12rem;
    }
    &.tiptap-align-right {
        margin-right: 12rem;
    }
}
.tiptap-indent-5 {
    &.tiptap-align-left {
        margin-left: 15rem;
    }
    &.tiptap-align-right {
        margin-right: 15rem;
    }
}
.tiptap-indent-6 {
    &.tiptap-align-left {
        margin-left: 18rem;
    }
    &.tiptap-align-right {
        margin-right: 18rem;
    }
}
</style>

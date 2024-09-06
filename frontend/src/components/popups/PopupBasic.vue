<template>
    <Teleport
        :to="$root.$el"
    >
        <div
            v-show="!hide"
            ref="rootEl"
            v-blur.teleport="blurParent || $parent"
            class="c-popup-basic rounded-lg bg-cm-00"
            :class="popupClasses"
            v-bind="$attrs"
            :style="popupStyle"
            @click.stop
        >
            <div
                v-if="showTriangle"
                class="c-popup-basic__triangle"
                :class="triangleBgColorClass"
                :style="triangleStyle"
            >

            </div>

            <div
                ref="container"
                class="c-popup-basic__container shadow-lg"
                :class="containerClass"
            >
                <slot
                    name="popupTop"
                >
                </slot>

                <div
                    :class="overflowClass"
                    :style="{ maxHeight: maxHeight + 'px' }"
                >
                    <slot>

                    </slot>
                </div>
            </div>

        </div>
    </Teleport>
</template>

<script>
import { warn } from 'vue';
import { addScrollListener, getScrollParent, removeScrollListener } from '@/core/helpers/scrolling.js';
import { isRemString, remToPx } from '@/core/utils.js';

const { max } = Math;

function getZIndex(el) {
    let z = 4;

    let parent = el;
    while (parent !== window.document.body) {
        try {
            const newZ = window.document.defaultView.getComputedStyle(parent).getPropertyValue('z-index');
            if (!_.isNaN(newZ) && newZ !== 'auto' && _.toNumber(newZ) > z) {
                z = newZ;
            }
            // eslint-disable-next-line no-empty
        } catch {}
        parent = parent.parentElement;
    }
    return z;
}

export default {
    name: 'PopupBasic',
    components: {

    },
    inject: {
        getModal: {
            default: null,
        },
    },
    props: {
        hide: Boolean,
        containerClass: {
            type: String,
            default: '',
        },
        // A HTML element that is used to align the popup in the right place.
        // The popup needs to be a root element in order to appear above
        // overflows.
        activator: {
            type: Object,
            required: true,
        },
        blurParent: {
            type: Object,
            default: null,
        },
        // Align the top of the popup with the bottom of the activator. This is
        // the default behaviour.
        // This will automatically flip the popup if it goes below the viewport.
        bottom: Boolean,
        // Align the bottom of the popup with the top of the activator.
        // This will automatically flip the popup if it goes above the viewport.
        top: Boolean,
        // Align the left of the popup with the right of the activator.
        right: Boolean,
        // Align the right of the popup with the left of the activator.
        left: Boolean,
        // Align the bottom of the popup with the bottom of the activator. Only
        // applies when the `left` or `right` prop is used.
        alignBottom: Boolean,
        // Align the top of the popup with the top of the activator. Only
        // applies when the `left` or `right` prop is used. This is the default
        // behaviour.
        alignTop: Boolean,
        // Align the right of the popup with the right of the activator. Only
        // applies when the `top` or `bottom` prop is used.
        alignRight: Boolean,
        // Align the left of the popup with the left of the activator. Only
        // applies when the `top` or `bottom` prop is used. This is the default
        // behaviour.
        alignLeft: Boolean,
        // Align the popup in the center of the activator vertically if the
        // `right` or `left` and `height` prop are used and horizontally if the `top` or
        // `bottom` and `width` prop are used.
        alignCenter: Boolean,

        // Move the popup right relative to it's alignment.
        nudgeRightProp: {
            type: String,
            default: '0rem',
            validator: (value) => isRemString(value),
        },
        nudgeLeftProp: {
            type: String,
            default: '0rem',
            validator: (value) => isRemString(value),
        },
        nudgeUpProp: {
            type: String,
            default: '0rem',
            validator: (value) => isRemString(value),
        },
        nudgeDownProp: {
            type: String,
            default: '0rem',
            validator: (value) => isRemString(value),
        },

        // Tells the popup to match the dimensions of the activator.
        matchWidth: Boolean,
        matchHeight: Boolean,

        // Manually specify the width of the popup (necessary for horizontal
        // center alignment).
        widthProp: {
            type: [String, null],
            default: null,
            validator: (value) => isRemString(value) || _.isNull(value),
        },
        // Manually specify the height of the popup (necessary for vertical
        // center alignment and positioning). This acts on the outer element.
        // This is not for scrolling on the popups.
        heightProp: {
            type: [String, null],
            default: null,
            validator: (value) => isRemString(value) || _.isNull(value),
        },
        // This value is for the css and the height of the popup container for the overflow.
        // This acts on the inner container. This is not for positioning.
        maxHeightProp: {
            type: [String, null],
            default: null,
            validator: (value) => isRemString(value) || _.isNull(value),
        },
        triangleSizeProp: {
            type: String,
            default: '1rem',
        },
        showTriangle: Boolean,
        triangleBgColorClass: {
            type: String,
            default: 'bg-cm-00',
        },
        zClass: {
            type: String,
            default: '',
        },
    },
    data() {
        return {
            triangleSize: remToPx(this.triangleSizeProp),
            nudgeRight: remToPx(this.nudgeRightProp),
            nudgeLeft: remToPx(this.nudgeLeftProp),
            nudgeUp: remToPx(this.nudgeUpProp),
            nudgeDown: remToPx(this.nudgeDownProp),
            width: remToPx(this.widthProp),
            height: remToPx(this.heightProp),
            maxHeight: remToPx(this.maxHeightProp),

            popupStyle: {},
            triangleStyle: {},
            previousBox: null,
            popupHeight: null,
            popupWidth: null,
            closeToBottom: false,
            closeToTop: false,
            closeToLeft: false,
            closeToRight: false,
            scrollingElement: null,
        };
    },
    computed: {
        // As bottom is the default behavior, this returns true if nothing else is set.
        bottomWithDefault() {
            return this.bottom || (!this.top && !this.right && !this.left);
        },
        alignLeftWithDefault() {
            return this.alignLeft || (!this.alignTop && !this.alignRight && !this.alignBottom);
        },
        // The props are in such a way to make it easy to use the dropdown and
        // prevent invalid props, but they are not easy to use in this
        // component. Instead we normalize them into a simple object that
        // says which side of the activator the popup should be, and how it
        // should be aligned on that side.
        // e.g. { side: 'bottom', align: 'left' } means the popup should be
        // on the bottom aligned to the left.
        positioning() {
            let side;
            let align;
            if (this.top) {
                side = 'top';
            } else if (this.right) {
                side = 'right';
            } else if (this.left) {
                side = 'left';
            } else {
                side = 'bottom';
            }

            if (this.alignCenter) {
                align = 'center';
            } else if (side === 'top' || side === 'bottom') {
                align = this.alignRight ? 'right' : 'left';
            } else {
                align = this.alignBottom ? 'bottom' : 'top';
            }
            return { side, align };
        },
        // If the popup is too close to the edge of the viewport, we want to
        // switch it to the opposite side that is specified in the props. So
        // the real positioning is sometimes different.
        realPositioning() {
            let { side, align } = this.positioning;
            const box = this.getActivatorBox();
            const popupWidth = this.popupWidth || this.calculateWidth(box);
            const popupHeight = this.popupHeight || this.calculateHeight(box);
            const closeToBottom = this.closeToBottom;
            const closeToTop = this.closeToTop;
            const closeToLeft = this.closeToLeft;
            const closeToRight = this.closeToRight;

            // If the popup is smaller than the activator than we can align it
            // normally, otherwise we need to override the alignment options.
            if (popupHeight > box.height) {
                // If the viewport is really small we just go with the defaults;
                if (closeToTop && !closeToBottom) {
                    if (side === 'top') {
                        side = 'bottom';
                    }
                    if (align === 'bottom') {
                        align = 'top';
                    }
                }

                if (closeToBottom && !closeToTop) {
                    if (side === 'bottom') {
                        side = 'top';
                    }
                    if (align === 'top') {
                        align = 'bottom';
                    }
                }
            }

            if (popupWidth > box.width) {
                if (closeToLeft && !closeToRight) {
                    if (side === 'left') {
                        side = 'right';
                    }
                    if (align === 'right' || align === 'center') {
                        align = 'left';
                    }
                }

                if (closeToRight && !closeToLeft) {
                    if (side === 'right') {
                        side = 'left';
                    }
                    if (align === 'left' || align === 'center') {
                        align = 'right';
                    }
                }
            }

            return { side, align };
        },
        positionedTopOrBottom() {
            return this.realPositioning.side === 'top' || this.realPositioning.side === 'bottom';
        },
        positionedLeftOrRight() {
            return this.realPositioning.side === 'left' || this.realPositioning.side === 'right';
        },
        // If the popup should be placed over the activator, but is close to the
        // top of the page then we swap.
        // If the popup should be placed under the activator, but is close to the
        // bottom of the page then we swap.
        swapTopBottom() {
            return this.positionedTopOrBottom && this.positioning.side !== this.realPositioning.side;
        },
        swapRightLeft() {
            return this.positionedLeftOrRight && this.positioning.side !== this.realPositioning.side;
        },
        // If the popup has been swapped to a different side due to constraints
        // in the viewport then any nudging should be inverted.
        realNudgeDown() {
            return this.swapTopBottom ? this.nudgeUp : this.nudgeDown;
        },
        realNudgeUp() {
            return this.swapTopBottom ? this.nudgeDown : this.nudgeUp;
        },
        realNudgeLeft() {
            return this.swapRightLeft ? this.nudgeRight : this.nudgeLeft;
        },
        realNudgeRight() {
            return this.swapRightLeft ? this.nudgeLeft : this.nudgeRight;
        },
        // There should be no case where the user wants to nudge left _and_ right
        // or up _and_ down but it's just easier here to merge the two props, and
        // it will apply the correct nudging when one of them is 0.
        verticalNudge() {
            return this.realNudgeDown - this.realNudgeUp;
        },
        horizontalNudge() {
            return this.realNudgeRight - this.realNudgeLeft;
        },
        overflowClass() {
            return this.maxHeight ? 'overflow-y-auto' : '';
        },
        popupClasses() {
            return this.zClass;
        },
    },
    methods: {
        updatePosition() {
            const box = this.getActivatorBox();
            const popupBox = this.getPopupBox();
            if (_.isEqual(box, this.previousBox)) {
                return;
            }
            this.previousBox = box;
            const scrollYEl = getScrollParent(this.getActivatorEl(), true);
            const scrollXEl = getScrollParent(this.getActivatorEl(), true, false);
            const scrollXBox = scrollXEl !== document.body && scrollXEl.getBoundingClientRect();
            const scrollYBox = scrollYEl !== document.body && scrollYEl.getBoundingClientRect();

            const height = this.calculateHeight(box);
            if (height) {
                this.popupStyle.height = `${height}px`;
            }

            const width = this.calculateWidth(box);
            if (width) {
                this.popupStyle.width = `${width}px`;
            }

            const popupHeight = popupBox?.height || height || this.maxHeight;
            const popupWidth = width || popupBox?.width;
            this.popupHeight = popupHeight;
            this.popupWidth = popupWidth;
            this.closeToBottom = (window.innerHeight - box.bottom) < popupHeight;
            this.closeToTop = box.top < popupHeight;
            this.closeToLeft = box.left < popupWidth;
            this.closeToRight = (window.innerWidth - box.right) < popupWidth;

            let top = this.calculateTopPosition(box);
            if (top && scrollYBox) {
                top = max(scrollYBox.top, top);
            }
            this.popupStyle.top = _.isNumber(top) ? `${top}px` : null;

            let bottom = this.calculateBottomPosition(box);
            if (bottom && scrollYBox) {
                bottom = max(document.body.offsetHeight - scrollYBox.bottom, bottom);
            }
            this.popupStyle.bottom = _.isNumber(bottom) ? `${bottom}px` : null;

            let left = this.calculateLeftPosition(box);
            if (left && scrollXBox) {
                left = max(scrollXBox.left, left);
            }
            this.popupStyle.left = _.isNumber(left) ? `${left}px` : null;

            let right = this.calculateRightPosition(box);
            if (right && scrollXBox) {
                right = max(document.body.offsetWidth - scrollXBox.right, right);
            }
            this.popupStyle.right = _.isNumber(right) ? `${right}px` : null;
            this.updateTriangleStyle();
        },
        // Triangle things
        updateTriangleStyle() {
            this.triangleStyle = {
                width: `${this.triangleSize}px`,
                height: `${this.triangleSize}px`,
                ...this.calculateTrianglePosition(),
            };
        },
        calculateTrianglePosition() {
            const align = this.realPositioning.align;
            const side = this.realPositioning.side;

            const box = this.getActivatorBox();
            const positionObj = {};
            const triangleSize = this.triangleSize;
            const halfTriangle = triangleSize / 2;
            const relevantDimension = (align === 'right' || align === 'left' ? box.width : box.height);
            const offset = (relevantDimension / 2) - halfTriangle;

            positionObj[align] = `${offset}px`;

            positionObj[{
                top: 'bottom',
                bottom: 'top',
                left: 'right',
                right: 'left',
            }[side]] = `-${halfTriangle}px`;

            return positionObj;
        },
        getActivatorEl() {
            // The activator prop could be a proxy object in which case _.has
            // would return false. Has in checks if the $el property is
            // accessible on the object.
            return _.hasIn(this.activator, '$el') ? this.activator.$el : this.activator;
        },
        getActivatorBox() {
            const activator = this.getActivatorEl();
            return activator.getBoundingClientRect();
        },
        getPopupEl() {
            return this.$refs.rootEl;
        },
        getPopupBox() {
            const popup = this.getPopupEl();
            return popup?.getBoundingClientRect();
        },
        calculateWidth(box) {
            return this.matchWidth ? box.width : this.width;
        },
        calculateHeight(box) {
            return this.matchHeight ? box.height : this.height;
        },
        calculateTopPosition(box) {
            const offset = this.verticalNudge;
            const { side, align } = this.realPositioning;
            if (side === 'bottom') {
                return box.bottom + offset;
            }
            if (align === 'center' && (side === 'left' || side === 'right')) {
                return box.top + ((box.height - this.calculateHeight(box)) / 2) + offset;
            }
            if (align === 'top') {
                return box.top + offset;
            }
            return null;
        },
        calculateBottomPosition(box) {
            const offset = document.body.offsetHeight - this.verticalNudge;
            const { side, align } = this.realPositioning;
            if (side === 'top') {
                return offset - box.top;
            }
            if (align === 'bottom') {
                return offset - box.bottom;
            }
            return null;
        },
        calculateRightPosition(box) {
            const offset = document.documentElement.clientWidth - this.horizontalNudge;
            const { side, align } = this.realPositioning;
            if (side === 'left') {
                return offset - box.left;
            }
            if (align === 'right') {
                return offset - box.right;
            }
            return null;
        },
        calculateLeftPosition(box) {
            const offset = this.horizontalNudge;
            const { side, align } = this.realPositioning;
            if (side === 'right') {
                return box.right + offset;
            }
            if (align === 'center' && (side === 'top' || side === 'bottom')) {
                return box.left + ((box.width - this.calculateWidth(box)) / 2) + offset;
            }
            if (align === 'left') {
                return box.left + offset;
            }
            return null;
        },
        scroll(x, y) {
            return this.$refs.container.scroll(x, y);
        },
        setZIndex(activator) {
            if (!this.zClass) {
                this.popupStyle.zIndex = getZIndex(activator) || 4;
            }
        },
    },
    watch: {
        verticalNudge: 'updatePosition',
        horizontalNudge: 'updatePosition',
        async hide() {
            await this.$nextTick();
            this.updatePosition();
        },
    },
    created() {
        // Validating props, some props cannot be used together.
        if (this.left + this.right + this.top + this.bottom > 1) {
            warn('Only one position prop `left`, `right`, `top`, `bottom` can be specified for the popup');
        }
        if ((this.alignBottom || this.alignTop) && !this.right && !this.left) {
            warn('The `alignBottom` and `alignTop` props can only be used when `right` or `left` props are set');
        }
        if ((this.alignRight || this.alignLeft) && (this.right || this.left)) {
            warn('The `alignRight` and `alignLeft` props can only be used when `top` or `bottom` props are set');
        }
        if (this.matchWidth && this.width) {
            warn('`matchWidth` and `width` props cannot be used at the same time');
        }
        if (this.matchHeight && this.height) {
            warn('`matchHeight` and `height` props cannot be used at the same time');
        }
        if (this.alignCenter) {
            if (this.right || this.left) {
                if (!this.height && !this.matchHeight) {
                    warn('`matchHeight` or `height` must be specified to center align');
                }
            } else if (!this.width && !this.matchWidth) {
                warn('`matchWidth` or `width` must be specified to center align');
            }
        }

        // The popup needs to move with the activator when scrolling.
        addScrollListener(this.updatePosition, this.getActivatorEl());
        this.updatePosition();

        // Please forgive this terrible piece of code.
        // Sometimes the activator has a transition in that resizes it.
        // For whatever reason the popup may not be positioned correctly.
        // Here we just ensure that, at the very least, it gets positioned every
        // half second.
        window.setTimeout(this.updatePosition, 500);
    },
    mounted() {
        const activator = this.getActivatorEl();
        this.setZIndex(activator);
        this.updatePosition();
    },
    unmounted() {
        const scrollParent = getScrollParent(this.getActivatorEl());
        removeScrollListener(this.updatePosition, scrollParent);
        window.clearTimeout(this.updatePosition);
    },
};
</script>

<style scoped>

.c-popup-basic {
    font-family: Figtree, sans-serif;
    -webkit-font-smoothing: antialiased;
    -moz-osx-font-smoothing: grayscale;

    @apply
        fixed
    ;

    &__container {
        @apply
            border
            border-cm-100
            border-solid
            relative
            rounded-lg
        ;
    }

    &__triangle {
        @apply
            absolute
            border
            border-cm-200
            border-solid
            rotate-45
            -z-1
        ;
    }
}
</style>

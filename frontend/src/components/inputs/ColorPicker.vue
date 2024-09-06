<!--
This component is not finished.
It is enough to work for the cases so far.
It will be improved as we go.
-->
<template>
    <div class="c-color-picker">
        <div
            v-if="colorIsCircle"
            class="relative center"
            tabindex="0"
        >
            <div
                class="c-color-picker__circle center"
                :class="[circleClasses, grayscaleOpacityClass]"
            >
            </div>

            <div
                class="c-color-picker__gray circle-center"
            >
                <div class="c-color-picker__center">
                </div>
            </div>

            <div
                ref="circle"
                class="absolute h-full w-full"
                :style="rotateStyle"
                @mousedown="onClickRotator"
                @touchdown="onClickRotator"
            >
                <div
                    v-if="!grayscale"
                    class="c-color-picker__dot c-color-picker__handle transition-2eio"
                >

                </div>
            </div>
        </div>
        <div>

            <div
                v-if="!colorIsCircle"
                class="flex justify-end "
            >
                <button
                    type="button"
                    class="font-semibold px-1 py-1p text-xxs rounded-full"
                    :class="grayscale ? 'bg-red-200 text-red-600' : 'bg-cm-300 text-cm-700'"
                    @click="grayscaleToggle"
                    @keyup.enter.stop
                >
                    <!-- {{ grayscale ? 'Color' : 'Grayscale' }} -->
                </button>
            </div>

            <div
                v-if="!colorIsCircle"
                :class="grayscaleOpacityClass"
            >
                <div
                    ref="colorSlider"
                    class="c-color-picker__bar c-color-picker__colored-bar"
                    :class="[barClasses, marginClass]"
                    tabindex="0"
                    @mousedown="onClickSlider($event, colorSlider)"
                    @touchdown="onClickSlider($event, colorSlider)"
                    @keyup.alt.right="goDownColor"
                    @keyup.alt.left="goUpColor"
                    @keyup.alt.down="goDownColor"
                    @keyup.alt.up="goUpColor"
                    @keyup.right.exact="goDownColor('big')"
                    @keyup.left.exact="goUpColor('big')"
                    @keyup.down.exact="goDownColor('big')"
                    @keyup.up.exact="goUpColor('big')"
                >
                    <div
                        v-show="!grayscale"
                        ref="colorSliderBall"
                        class="c-color-picker__ball c-color-picker__handle transition-2eio"
                        :style="ballStyle"
                    >
                    </div>
                </div>
            </div>

            <div
                ref="containerSlider"
                class="c-color-picker__bar"
                :class="[barClasses, marginClass]"
                :style="barBackground"
                tabindex="0"
                @mousedown="onClickSlider($event, slider)"
                @touchdown="onClickSlider($event, slider)"
                @keyup.alt.right="goDarker"
                @keyup.alt.left="goLighter"
                @keyup.alt.down="goDarker"
                @keyup.alt.up="goLighter"
                @keyup.right.exact="goDarker('big')"
                @keyup.left.exact="goLighter('big')"
                @keyup.down.exact="goDarker('big')"
                @keyup.up.exact="goLighter('big')"
            >
                <div
                    ref="sliderBall"
                    class="c-color-picker__ball c-color-picker__handle transition-2eio"
                    :style="ballStyle"
                >
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import Rotator from '@/core/ui/Rotator.js';
import Slider from '@/core/ui/Slider.js';

export default {

    name: 'ColorPicker',
    components: {

    },
    mixins: [
    ],
    props: {
        modelValue: {
            type: String,
            default: 'hsl(0, 100%, 50%)',
        },
        colorPickerShape: {
            type: String,
            default: 'circle',
        },
        circleClasses: {
            type: String,
            default: 'h-40 w-40',
        },
        barClasses: {
            type: String,
            default: 'h-40 w-3',
        },
        barOrientation: {
            type: String,
            default: 'vertical',
        },
    },
    emits: [
        'update:modelValue',
    ],
    data() {
        return {
            rotator: null,
            slider: null,
            colorSlider: null,
        };
    },
    computed: {
        rotateStyle() {
            return {
                transform: `rotate(${this.hue}deg)`,
            };
        },
        colorParts() {
            return this.modelValue.slice(4, -1).split(/,\s*/);
        },
        hue() {
            return +this.colorParts[0];
        },
        saturation() {
            return +this.colorParts[1].slice(0, -1);
        },
        lightness() {
            return +this.colorParts[2].slice(0, -1);
        },
        grayscale() {
            return this.saturation === 0;
        },
        barBackground() {
            const turn = this.barIsVertical ? '' : 'to right,';
            const bg = `linear-gradient(
                ${turn}
                hsl(${this.hue}, ${this.saturation}%, 60%),
                hsl(${this.hue}, ${this.saturation}%, 0%))`;
            return {
                backgroundImage: bg,
            };
        },
        grayscaleOpacityClass() {
            return this.grayscale ? 'opacity-50' : 'opacity-100';
        },
        colorIsCircle() {
            return this.colorPickerShape === 'circle';
        },
        barIsVertical() {
            return this.barOrientation === 'vertical';
        },
        marginClass() {
            return this.barIsVertical ? 'ml-3' : 'mt-2';
        },
        ballStyle() {
            const ballOffset = this.barIsVertical ? { left: '-0.25rem' } : { top: '-0.25rem' };
            return {
                ...ballOffset,
            };
        },
    },
    methods: {
        isInGray(event) {
            const circle = this.$refs.circle;
            const rect = circle.getBoundingClientRect();
            const cx = rect.left + rect.width / 2;
            const cy = rect.top + rect.height / 2;

            const radius = Math.sqrt(((event.clientY - cy) ** 2) + ((event.clientX - cx) ** 2));

            return radius < (circle.clientWidth / 2) * 0.66;
        },
        onClickRotator(event) {
            if (this.isInGray(event)) {
                event.stopPropagation();
                this.$emit('update:modelValue', `hsl(${Math.floor(this.hue)}, 0%, ${this.lightness}%)`);
                return;
            }

            this.rotator.setAngleFromEvent(event);
        },
        grayscaleToggle() {
            // If already grayscale, switch. If not grayscale, now we want grayscale.
            const saturation = this.grayscale ? '100%' : '0%';
            this.$emit('update:modelValue', `hsl(${this.hue}, ${saturation}, ${this.lightness}%)`);
        },
        onClickSlider(event, slider) {
            slider.setPositionFromEvent(event);
        },
        lightnessToPosition(lightness) {
            return 100 - (lightness * (100 / 60));
        },
        positionToLightness(position) {
            return Math.floor(60 - ((position * 60) / 100));
        },
        hueToPosition(hue) {
            return (hue * (100 / 360));
        },
        positionToHue(position) {
            return Math.floor(position * (360 / 100));
        },
        goDarker(step = '') {
            const stepSize = step === 'big' ? 10 : 5;
            const newLightness = this.lightness <= 0 ? 60 : this.lightness - stepSize;
            return this.$emit(
                'update:modelValue',
                `hsl(${this.hue}, ${this.saturation}%, ${newLightness}%)`
            );
        },
        goLighter(step = '') {
            const stepSize = step === 'big' ? 10 : 5;
            const newLightness = this.lightness >= 60 ? 0 : this.lightness + stepSize;
            return this.$emit(
                'update:modelValue',
                `hsl(${this.hue}, ${this.saturation}%, ${newLightness}%)`
            );
        },
        goUpColor(step = '') {
            const stepSize = step === 'big' ? 20 : 5;
            const newHue = this.hue <= 0 ? 360 : this.hue - stepSize;
            return this.$emit(
                'update:modelValue',
                `hsl(${newHue}, ${this.saturation}%, ${this.lightness}%)`
            );
        },
        goDownColor(step = '') {
            const stepSize = step === 'big' ? 20 : 5;
            const newHue = this.hue >= 360 ? 0 : this.hue + stepSize;
            return this.$emit(
                'update:modelValue',
                `hsl(${newHue}, ${this.saturation}%, ${this.lightness}%)`
            );
        },
    },
    watch: {
        hue(hue) {
            if (this.colorIsCircle) {
                this.rotator.angle = hue;
            } else {
                this.colorSlider.position = this.hueToPosition(hue);
            }
        },
        lightness(lightness) {
            this.slider.position = this.lightnessToPosition(lightness);
        },
    },
    mounted() {
        if (this.colorIsCircle) {
            this.rotator = new Rotator(this.$refs.circle, {
                angle: this.hue,
                onRotate: _.throttle((hue) => {
                    this.$emit('update:modelValue', `hsl(${Math.floor(hue)}, 100%, ${this.lightness}%)`);
                }, 100),
                onDragStart: (event) => !this.isInGray(event),
            });
        }

        if (!this.colorIsCircle) {
            this.colorSlider = new Slider(
                this.$refs.colorSlider,
                this.$refs.colorSliderBall,
                {
                    position: this.hueToPosition(this.hue),
                    onSlide: _.throttle((position) => {
                        this.$emit('update:modelValue',
                            `hsl(${this.positionToHue(position)},
                            100%,
                            ${this.lightness}%)`);
                    }, 100),
                    // onDragStart: event => !this.isInGray(event),
                },
                this.barOrientation
            );
        }

        this.slider = new Slider(
            this.$refs.containerSlider,
            this.$refs.sliderBall,
            {
                position: this.lightnessToPosition(this.lightness),
                onSlide: _.throttle(
                    (position) => this.$emit(
                        'update:modelValue',
                        `hsl(${this.hue}, ${this.saturation}%, ${this.positionToLightness(position)}%)`
                    ),
                    100
                ),
            },
            this.barOrientation
        );
    },
    created() {

    },
};
</script>

<style scoped>
.c-color-picker {
    @apply
        flex
    ;

    &__circle {
        background-image: conic-gradient(#f00, #ff0, #0f0, #0ff, #00f, #f0f, #f00);

        @apply
            rounded-full
        ;
    }

    /* stylelint-disable */
    &__colored-bar {
        background-image: linear-gradient(to right, #f00, #ff0, #0f0, #0ff, #00f, #f0f, #f00);
    }
    /* stylelint-enable */

    &__gray {
        background-color: #808080;
        height: 66%;
        width: 66%;

        @apply
            absolute
        ;
    }

    &__center {
        height: 80%;
        width: 80%;

        @apply
            bg-cm-00
            rounded-full
        ;
    }

    &__dot {
        height: 12%;
        left: 44%;
        top: 3%;
        width: 12%;
    }

    &__handle {
        transition-property: box-shadow;

        @apply
            absolute
            bg-cm-00
            border
            border-cm-300
            border-solid
            cursor-pointer
            rounded-full
            shadow-center-dark
        ;

        &:hover {
            @apply
                shadow-center-darker
            ;
        }
    }

    &__bar {
        @apply
            relative
            rounded-full
            z-over
        ;
    }

    &__ball {
        @apply
            h-5
            w-5
        ;
    }
}
</style>

<template>
    <div
        class="c-loading-bar rounded-full bg-cm-100"
        :class="barSizeClasses"
    >
        <div
            class="c-loading-bar__bar h-full rounded-full transition-2eio"
            :style="barStyle"
            :class="barColor"
        >
        </div>
    </div>
</template>

<script>

export default {
    name: 'LoadingBar',
    components: {

    },
    mixins: [
    ],
    props: {
        isTotallyFake: Boolean,
        percentage: {
            type: Number,
            default: 0,
        },
        barSizeClasses: {
            type: String,
            default: 'w-48 h-5',
        },
        barColor: {
            type: String,
            default: 'bg-primary-500',
        },
    },
    data() {
        return {
            fakeBarWidth: 10,
        };
    },
    computed: {
        barStyle() {
            return { width: this.barPercentage };
        },
        barPercentage() {
            return `${this.barWidth}%`;
        },
        barWidth() {
            if (this.isTotallyFake) {
                return this.fakeBarWidth;
            }
            return this.percentage;
        },
    },
    methods: {
        timeout(ms) {
            return new Promise((resolve) => {
                setTimeout(resolve, ms);
            });
        },
        async keepIncreasingBar() {
            for (; this.fakeBarWidth < 88; this.fakeBarWidth += _.random(6, 12)) {
                const randomTime = _.random(1000, 4000);
                /* eslint-disable-next-line */
                await this.timeout(randomTime);
            }
        },
    },
    created() {
        if (this.isTotallyFake) {
            this.keepIncreasingBar();
        }
    },
};
</script>

<style scoped>

.c-loading-bar {
    &__bar {
        animation: roll 10s linear infinite alternate;
        background-image: repeating-linear-gradient(
            -45deg,
            transparent 0 10px,
            rgba(255,255,255,0.3) 10px 20px
        );
        background-size: 200% 200%;
    }

    @keyframes roll {
        100% {
            background-position: 100% 100%;
        }
    }
}

</style>

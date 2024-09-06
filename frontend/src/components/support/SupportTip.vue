<template>
    <PopupBasic
        class="o-support-tip"
        widthProp="15rem"
        nudgeDownProp="0.625rem"
        :showTriangle="true"
        triangleBgColorClass="bg-secondary-100"
        v-bind="$attrs"
    >
        <div
            v-blur="closeTips"
            class="bg-secondary-100 text-sm pt-5 pb-2 px-2 rounded-lg text-center relative"
        >
            <CloseButton
                class="absolute top-px right-px"
                @click.stop="closeTips"
            >
            </CloseButton>

            <h4 class="font-bold mb-2 leading-tight">
                {{ title }}
            </h4>
            <p>
                {{ text }}
            </p>

            <div
                class="flex justify-center items-center mt-2"
            >
                <ButtonEl
                    class="py-1 px-2"
                    @click="moveTip('BACK')"
                >
                    <i class="fas fa-angle-left">
                    </i>
                </ButtonEl>

                <span>
                    {{ tipNumber }} / {{ tipsLength }}
                </span>

                <ButtonEl
                    class="py-1 px-2"
                    @click="moveTip('NEXT')"
                >
                    <i class="fas fa-angle-right">
                    </i>
                </ButtonEl>
            </div>
        </div>
    </PopupBasic>
</template>

<script>

import CloseButton from '@/components/buttons/CloseButton.vue';

import UPDATE_ACTIVE_TIPS from '@/graphql/client/UpdateActiveTips.gql';

export default {
    name: 'SupportTip',
    components: {
        CloseButton,
    },
    mixins: [
    ],
    props: {
        tips: {
            type: Array,
            required: true,
        },
        tip: {
            type: Object,
            required: true,
        },
    },
    data() {
        return {

        };
    },
    computed: {
        title() {
            return this.tip.tipTitle;
        },
        text() {
            return this.tip.tipText;
        },
        tipsLength() {
            return this.tips.length;
        },
        tipIndex() {
            return this.tips.indexOf(this.tip);
        },
        tipNumber() {
            return this.tipIndex + 1;
        },
    },
    methods: {
        moveTip(direction) {
            const tipsVal = _.cloneDeep(this.tips);
            tipsVal[this.tipIndex].active = false;

            if (direction === 'NEXT') {
                if ((this.tipsLength - 1) > this.tipIndex) {
                    tipsVal[this.tipIndex + 1].active = true;
                } else {
                    tipsVal[0].active = true;
                }
            } else if (direction === 'BACK') {
                if (this.tipIndex > 0) {
                    tipsVal[this.tipIndex - 1].active = true;
                } else {
                    tipsVal[this.tipsLength - 1].active = true;
                }
            }
            this.mutateTips(tipsVal);
        },
        closeTips() {
            this.mutateTips([]);
        },
        mutateTips(tipsVal) {
            this.$apollo.mutate({
                mutation: UPDATE_ACTIVE_TIPS,
                variables: {
                    tips: tipsVal,
                },
                client: 'defaultClient',
            });
        },
    },
    created() {

    },
};
</script>

<style scoped>

/*.o-support-tip {

} */

</style>

<template>
    <div
        class="o-email-from"
        :class="imageSize"
    >
        <ProfileNameImage
            v-if="fromUser"
            :profile="user"
            :hideFullName="true"
        >
        </ProfileNameImage>

        <template
            v-else-if="from"
        >
            <ImageOrFallback
                class="bg-primary-100 text-primary-600 font-medium"
                :class="imageClasses"
                imageClass="rounded-lg"
                :name="from"
            >
            </ImageOrFallback>
        </template>
    </div>
</template>

<script>

import ProfileNameImage from '@/components/profile/ProfileNameImage.vue';
import Email from '@/core/models/Email.js';

import { getColorName } from '@/core/display/nameColors.js';

export default {
    name: 'EmailFrom',
    components: {
        ProfileNameImage,
    },
    mixins: [
    ],
    props: {
        email: {
            type: Email,
            required: true,
        },
        size: {
            type: String,
            default: 'base',
            validator(val) {
                return ['base', 'sm'].includes(val);
            },
        },
    },
    data() {
        return {

        };
    },
    computed: {
        fromEmail() {
            return this.email.from?.address;
        },
        from() {
            return this.email.fromName();
        },
        user() {
            return this.$root.authenticatedUser;
        },
        fromUser() {
            return this.email.isFromAccountOwner();
        },

        imageSize() {
            return this.size === 'sm' ? 'h-7 w-7' : 'h-10 w-10';
        },
        imageClasses() {
            return `${this.imageSize} bg-${this.color}-100 text-${this.color}-600`;
        },
        color() {
            return getColorName(this.from[0], this.from[1], this.fromUser);
        },
    },
    methods: {
    },
    created() {

    },
};
</script>

<style scoped>

/*.o-email-from {

} */

</style>

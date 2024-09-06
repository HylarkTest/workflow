import config from '@/core/config.js';
import { setCookie } from '@/core/helpers/cookieHelpers.js';

export default {
    emits: [
        'closeCookiesPanel',
        'closeCookiesBanner',
    ],
    data() {
        return {

        };
    },
    computed: {
        landingUrl() {
            return config('app.landing-url');
        },
        cookiePolicyLink() {
            return `${this.landingUrl}/cookies-policy`;
        },
        privacyPolicyLink() {
            return `${this.landingUrl}/privacy-policy`;
        },
        birdUrl() {
            return `${config('app.api-url')}/branding/ThumbsUpBird_72dpi.png`;
        },
    },
    methods: {
        submitCookie(form) {
            setCookie('hylark_cookies_permissions', form);

            // Banner is closed via global listener so only need to close panel here
            this.closePanel();
        },
        acceptAll() {
            this.submitCookie({ functional: true, analytics: true });
        },
        rejectAll() {
            this.submitCookie({ functional: false, analytics: false });
        },
        closePanel() {
            this.$emit('closeCookiesPanel');
        },
    },
};

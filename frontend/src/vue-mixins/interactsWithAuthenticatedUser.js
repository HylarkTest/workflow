import * as auth from '@/core/auth.js';

export default {
    data() {
        return {
            authenticatedUser: auth.getAuthenticatedUser(),
        };
    },
    computed: {
        isGuest() {
            return !this.authenticatedUser;
        },
        isVerified() {
            return this.authenticatedUser?.verified;
        },
        isAuthenticated() {
            return !!this.authenticatedUser;
        },
    },
};

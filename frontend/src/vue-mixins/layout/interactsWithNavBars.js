// import { logout } from '@/core/auth.js';

import { isMac } from '@/core/helpers/UserAgentHelpers.js';

export default {
    props: {
        extras: {
            type: Array,
            required: true,
        },
        icons: {
            type: Array,
            required: true,
        },
        links: {
            type: Object,
            required: true,
        },
        spaces: {
            type: Array,
            required: true,
        },
        pages: {
            type: Array,
            required: true,
        },
        isSupportOpen: Boolean,
    },
    emits: [
        'openFinderModal',
        'openSupportModal',
    ],
    data() {
        return {
            showAllPages: false,
        };
    },
    computed: {
        user() {
            return this.$root.authenticatedUser;
        },
    },
    methods: {
        // signOut() {
        //     logout();
        // },
        runAction(action) {
            if (_.isFunction(action)) {
                action();
            } else {
                this[action]();
            }
        },
        openSupportModal() {
            this.$emit('openSupportModal');
        },
        openFinderModal() {
            this.$emit('openFinderModal');
        },
        isSupportActive(link) {
            return link.val === 'SUPPORT' && this.isSupportOpen;
        },
        openFinderOnKeydown(event) {
            if (isMac()) {
                if (event.metaKey && event.key === 'k') {
                    this.openFinderModal();
                }
            }
            if (event.ctrlKey && event.key === 'k') {
                this.openFinderModal();
            }
        },
    },
    created() {
    },
    mounted() {
        document.addEventListener('keydown', (e) => this.openFinderOnKeydown(e));
    },
    beforeUnmount() {
        document.removeEventListener('keydown', (e) => this.openFinderOnKeydown(e));
    },
};

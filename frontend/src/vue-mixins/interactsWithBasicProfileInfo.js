export default {
    props: {
        profile: {
            type: Object,
            required: true,
        },
    },
    computed: {
        profileName() {
            return this.profile?.name || '';
        },
        profileAvatar() {
            return this.profile?.avatar || this.profile?.image;
        },
        profileEmail() {
            return this.profile?.email;
        },
        profileIsCollaborative() {
            return this.profile?.baseType === 'COLLABORATIVE';
        },
    },
};

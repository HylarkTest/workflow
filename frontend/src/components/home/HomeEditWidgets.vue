<template>
    <div class="o-home-edit-widgets">
        <div class="mb-6">
            <h5
                class="font-semibold mb-1"
            >
                Customize base link
            </h5>

            <div class="flex gap-6">
                <CheckHolder
                    v-for="option in radioOptions"
                    :key="option"
                    v-model="userForm.preferences.homepage.shortcuts.customize"
                    :val="option"
                    type="radio"
                >
                    {{ getRadioOptionName(option) }}
                </CheckHolder>
            </div>
        </div>
        <div>
            <h5
                class="font-semibold mb-1"
            >
                Integrations link
            </h5>
            <div class="flex gap-6">
                <CheckHolder
                    v-for="option in radioOptions"
                    :key="option"
                    v-model="userForm.preferences.homepage.shortcuts.integrations"
                    :val="option"
                    type="radio"
                >
                    {{ getRadioOptionName(option) }}
                </CheckHolder>
            </div>
        </div>
    </div>
</template>

<script>

import interactsWithAuthenticatedUser from '@/vue-mixins/interactsWithAuthenticatedUser.js';
import { updateProfile } from '@/core/repositories/baseRepository.js';

const radioOptions = ['FULL', 'SMALL', 'HIDE'];

export default {
    name: 'HomeEditWidgets',
    components: {

    },
    mixins: [
        interactsWithAuthenticatedUser,
    ],
    props: {

    },
    data() {
        return {
            userForm: null,
        };
    },
    computed: {

    },
    methods: {
        getRadioOptionName(option) {
            const camelOption = _.camelCase(option);
            return this.$t(`home.customize.shortcutOptions.${camelOption}`);
        },
        setUserForm() {
            this.userForm = this.$apolloForm(() => {
                return {
                    preferences: {
                        homepage: {
                            shortcuts: _.clone(this.authenticatedUser.baseSpecificPreferences().homepage.shortcuts),
                        },
                    },
                };
            }, { client: 'defaultClient' });
        },
        async saveUserForm() {
            await updateProfile(this.userForm);
            this.$debouncedSaveFeedback();
        },
    },
    watch: {
        'userForm.preferences.homepage.shortcuts': {
            handler(newVal, oldVal) {
                if (oldVal) {
                    this.saveUserForm();
                }
            },
            deep: true,
        },
    },
    created() {
        this.radioOptions = radioOptions;
        this.setUserForm();
    },
};
</script>

<style scoped>

/*.o-home-edit-widgets {

} */

</style>

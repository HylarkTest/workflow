// Use the router prop or the data in your component (depending if the instructions
// are passed in or only in the component using the tabs);

export default {
    props: {
        useRouter: Boolean,
    },
    data() {
        return {
            // Set in component
            componentKey: '',
            selectedTab: '',
            paramKey: '',
            router: false,
        };
    },
    computed: {
        hasRouter() {
            return this.router || this.useRouter;
        },
        selectedPointer() {
            return this.hasRouter ? this.routeTab : this.selectedTab;
        },
        selectedComponent() {
            return _.pascalCase(this.componentKey) + _.pascalCase(this.selectedPointer);
        },
        routeTab() {
            return false; // Define in component where using router
        },
    },
    methods: {
        selectTab(tab, router = false) {
            if (router) {
                return;
            }
            this.selectedTab = tab.value;
        },
        routerParamDefault() {
            const first = this.tabs[0];
            this.$router.replace({
                name: first.link || this.$route.name,
                params: {
                    ...this.$route.params,
                    [this.paramKey]: first.paramName,
                },
            });
        },
    },
    watch: {
        $route: {
            immediate: true,
            handler(newVal, oldVal) {
                if (this.hasRouter) {
                    const params = newVal.params;
                    const noChange = !oldVal
                        || (newVal.name === oldVal.name);
                    if (noChange && this.paramKey && !params[this.paramKey]) {
                        this.routerParamDefault();
                    }
                }
            },
        },
    },
    // created() {
    //     if (this.paramKey && !this.$route.params[this.paramKey]) {
    //         this.routerParamDefault();
    //     }
    // },
};

<template>
    <div class="o-page-wizard-folder">
        <div class="max-w-xl">
            <h2 class="o-creation-wizard__prompt pt-20">
                Do you wish to categorize "{{ pageForm.name }}" in one of your folders?
            </h2>

            <div
                class="px-1 mb-4"
                :class="isNewFolder ? 'opacity-100' : 'opacity-0 pointer-events-none'"
            >
                <InputBox
                    ref="folderInput"
                    v-model="folderInput"
                    bgColor="gray"
                    placeholder="Your new folder's name"
                    :maxLength="60"
                >
                </InputBox>
            </div>

            <div class="flex flex-wrap justify-center">
                <div
                    v-for="option in options"
                    :key="option"
                    class="w-1/2 p-1"
                    tabindex="0"
                    @keyup.enter="setFolder(option.val)"
                    @keyup.space="setFolder(option.val)"
                >
                    <div
                        class="button bg-cm-100 w-full hover:shadow-lg h-full"
                    >
                        <CheckHolder
                            :modelValue="foldersVal"
                            :val="option.val"
                            type="radio"
                            @update:modelValue="setFolder"
                        >
                            <span class="text-sm">
                                {{ option.name }}
                            </span>
                        </CheckHolder>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>

// import LINKS from '@/graphql/Links.gql';

export default {
    name: 'PageWizardFolder',
    components: {

    },
    mixins: [
    ],
    props: {
        pageForm: {
            type: Object,
            required: true,
        },
        space: {
            type: Object,
            required: true,
        },
    },
    // apollo: {
    //     links: {
    //         query: LINKS,
    //         update: _.identity,
    //         fetchPolicy: 'cache-first',
    //     },
    // },
    emits: [
        'update:pageForm',
    ],
    data() {
        return {
            showNewInput: false,
        };
    },
    computed: {
        folderInput: {
            get() {
                return this.pageForm.folder?.replace(/\/$/, '') || '';
            },
            set(val) {
                const newVal = val ? `${val}/` : null;
                this.emitFolder(newVal, 'SAME');
            },
        },
        foldersVal() {
            if (this.isNewFolder) {
                return 'NEW';
            }
            const folder = this.pageForm.folder;
            if (folder && this.currentFolderExists) {
                return folder;
            }
            return 'NONE';
        },
        currentFolderExists() {
            return _.find(this.foldersWithName, { folder: this.pageForm.folder });
        },
        isNewFolder() {
            return this.showNewInput || (this.pageForm.folder && !this.currentFolderExists);
        },
        spaceFolders() {
            return this.space.folders;
        },
        foldersWithName() {
            return this.spaceFolders.filter((folder) => {
                return folder.folder;
            });
        },
        folders() {
            return this.foldersWithName.map((folder) => {
                const name = folder.folder.replace(/\/$/, '');
                return {
                    name,
                    val: folder.folder,
                };
            });
        },
        options() {
            return [
                {
                    name: 'None',
                    val: 'NONE',
                },
                {
                    name: 'New folder',
                    val: 'NEW',
                },
                ...this.folders,
            ];
        },
        // folders() {
        //     return _.uniq(this.links.spaces.edges.flatMap((edge) => {
        //         return edge.node.pages.edges.map((pageEdge) => pageEdge.node.folder);
        //     }));
        // },
    },
    methods: {
        setFolder(val) {
            let newVal;
            let nextStep = 'SAME';
            if (val === 'NONE') {
                this.showNewInput = false;
                nextStep = 'NEXT';
            } else if (val === 'NEW') {
                this.showNewInput = true;
                this.$nextTick(() => {
                    this.folderInput = 'New folder';
                    this.$nextTick(() => {
                        this.$refs.folderInput.select();
                    });
                });
            } else {
                this.showNewInput = false;
                newVal = val;
                nextStep = 'NEXT';
            }
            this.emitFolder(newVal, nextStep);
        },
        emitFolder(newVal, nextStep) {
            this.$emit('update:pageForm', { valKey: 'folder', newVal, nextStep });
        },
    },
    created() {

    },
};
</script>

<style scoped>

/*.o-page-wizard-folder {

} */

</style>

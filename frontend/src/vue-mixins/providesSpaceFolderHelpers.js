export default {
    methods: {
        getFolderName(folder) {
            return folder.slice(0, -1);
        },
        groupedByFolder(pages) {
            const folders = _.groupBy(pages, 'folder');
            const folderKeys = _(folders).keys().sortBy((key) => key.length !== 0).value();
            return folderKeys.map((folderKey) => {
                return {
                    folder: folderKey,
                    pages: folders[folderKey],
                };
            });
        },
    },
};

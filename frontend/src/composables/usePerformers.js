// WIP, to be expanded
export default () => {
    const getPerformerObj = (performer) => {
        if (!performer) {
            return null;
        }
        return {
            image: performer.avatar,
            name: performer.name,
        };
    };

    return {
        getPerformerObj,
    };
};

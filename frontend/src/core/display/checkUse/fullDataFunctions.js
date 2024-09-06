export function makeContainer(container, dataType = 'FIELDS') {
    return {
        elementType: 'CONTAINER',
        name: container.name,
        id: container.id,
        type: container.type,
        containerId: Math.random(),
        dataType,
    };
}

export function makeContainers(containersArr, dataType) {
    return containersArr.map((container) => {
        return makeContainer(container, dataType);
    });
}

export function makeRow(rowData, dataType) {
    return {
        elementType: 'ROW',
        id: Math.random(),
        containers: makeContainers([rowData], dataType),
    };
}

export function makeRows(dataArr, dataType) {
    return dataArr.map((row) => {
        return makeRow(row, dataType);
    });
}

export function makeSub(subData, dataType) {
    return {
        elementType: 'SUB',
        subName: subData.name,
        id: Math.random(),
        elements: makeRows(subData.options.fields, dataType),
    };
}

export function makeRowsOrSub(dataArr, dataType) {
    return dataArr.map((obj) => {
        if (obj.type === 'MULTI') {
            return makeSub(obj, dataType);
        }
        return makeRow(obj, dataType);
    });
}

export default {};

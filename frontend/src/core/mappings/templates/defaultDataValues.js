function getShortName(itemObj) {
    const extras = itemObj.extras;
    let nameVal;
    if (itemObj.FIRST_NAME || extras?.FIRST_NAME) {
        nameVal = itemObj.FIRST_NAME || extras.FIRST_NAME;
    } else if (itemObj.NAME) {
        nameVal = itemObj.NAME;
    } else {
        nameVal = itemObj.SYSTEM_NAME;
    }
    return nameVal.fieldValue;
}

function getShortNameFormatted(itemObj) {
    let nameVal = getShortName(itemObj);
    nameVal = _.toLower(nameVal);
    return nameVal.replace(/ /g, '.');
}

export function email(itemObj) {
    const formatted = getShortNameFormatted(itemObj);
    return `${formatted}-example@hylark.com`;
}

export function phone() {
    const random1 = _.random(100, 999);
    const random2 = _.random(1000, 9999);
    return `(000) ${random1}-${random2}`;
}

export function link(itemObj) {
    const formatted = getShortNameFormatted(itemObj);
    return `https://hylark/${formatted}-example.com`;
}

export function birthday() {
    const day = _.random(1, 28);
    const month = _.random(1, 12);
    const year = _.random(1950, 2000);

    return `${year}/${month}/${day}`;
}

export function image() {
    const random = _.random(1, 20);
    return `images/defaultItems/${random}.png`;
}

export function priorities() {
    const arr = [0, 1, 3, 5, 9];
    return arr[_.random(0, 4)];
}

export function favorites() {
    const arr = [true, false];
    return arr[_.random(0, 1)];
}

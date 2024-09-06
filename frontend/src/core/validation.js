export function checkIsEmailValid(emailAddress) {
    const checker = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return checker.test(emailAddress);
}

export function checkIsUrlValid(url) {
    // Add (unused now)
    return url;
}

export const validFileTypes = {
    IMAGE: {
        acceptedTypes: 'image/*',
        validator: (file) => file.type.includes('image/'),
    },
};

// Validate filetype based on feature type. Currently only used for Pins, this may change in the future.
// if no featureType is provided, any file type is accepted. This may also change in the future.
export function checkIsFileTypeValid(file, fileTypeKey) {
    const fileTypeObj = validFileTypes[fileTypeKey];
    if (fileTypeObj) {
        return fileTypeObj.validator(file);
    }
    return true;
}

// exceptions may change in the future
export const characterValidationMap = {
    numberOnly: {
        regex: /^[0-9]$/,
        exceptions: ['Backspace', 'ArrowLeft', 'ArrowDown', 'ArrowUp', 'ArrowRight', 'Enter', 'Tab'],
    },
    letterOnly: {
        regex: /^[A-Za-z]$/,
        exceptions: ['Backspace', 'ArrowLeft', 'ArrowDown', 'ArrowUp', 'ArrowRight', 'Enter', 'Tab'],
    },
    numberOrLetterOnly: {
        regex: /^[A-Za-z0-9]$/,
        exceptions: ['Backspace', 'ArrowLeft', 'ArrowDown', 'ArrowUp', 'ArrowRight', 'Enter', 'Tab'],
    },
};

export function validateInputByCharacterType(input, type) {
    return characterValidationMap[type].regex.test(input)
        || characterValidationMap[type].exceptions.includes(input);
}

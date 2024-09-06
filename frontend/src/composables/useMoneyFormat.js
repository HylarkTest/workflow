// Import the user's selected money format from the preferences repository
import {
    moneyFormat,
} from '@/core/repositories/preferencesRepository.js';

function sanitizeAmount(amountString) {
    // PURPOSE
    // Takes an amount (string) and removes the separators and standardizes the decimals

    // ARGUMENTS
    // **amountString** - The amount as a STRING.

    // OUTPUT
    // This function returns a STRING that has had the separators removed and decimals made into a period.

    // Remove spaces (which might be used as thousand separators)
    let standardizedAmount = amountString.replace(/\s/g, '');

    // Determine which character is used as a decimal separator (either dot or comma)
    const lastDotIndex = standardizedAmount.lastIndexOf('.');
    const lastCommaIndex = standardizedAmount.lastIndexOf(',');

    if (lastDotIndex > lastCommaIndex) {
        // The dot is the decimal separator
        // Remove all commas used as thousand separators
        standardizedAmount = standardizedAmount.replace(/,/g, '');
    } else if (lastCommaIndex > lastDotIndex) {
        // The comma is the decimal separator
        // Remove all dots used as thousand separators
        standardizedAmount = standardizedAmount.replace(/\./g, '');
        // Convert comma to dot as the decimal separator
        standardizedAmount = standardizedAmount.replace(/,/g, '.');
    }

    return standardizedAmount;
}

export default function useMoneyFormat() {
    const checkForValidChars = (amountInput) => {
        // PURPOSE
        // Takes the user's input and checks if it has invalid characters
        // No letters, no symbols other than decimals and separators (period, comma, empty space)

        // ARGUMENTS
        // **amountInput** - The user typed input, a STRING.

        // OUTPUT
        // This function returns a BOOLEAN, true if the input has only valid characters, false
        // if there are letters or other symbols

        // CAUTION
        // This does not check for valid format, just valid characters

        // Check for valid format
        const hasValidChars = /^[0-9.,\s]*$/.test(amountInput);

        return hasValidChars;
    };
    const checkForValidFormat = (amountInput) => {
        // PURPOSE
        // Takes the user's input and checks if it has a valid format
        // This means it checks characters as well as decimal places (2 max for now)

        // ARGUMENTS
        // **amountInput** - The user typed input, a STRING.

        // OUTPUT
        // This function returns a BOOLEAN

        // Check for valid chars
        const isValid = checkForValidChars(amountInput);

        if (!isValid) {
            return false;
        }

        // Remove spaces (which might be used as thousand separators)
        let standardizedAmount = amountInput.replace(/\s/g, '');

        // Determine which character is used as a decimal separator (either dot or comma)
        const lastDotIndex = standardizedAmount.lastIndexOf('.');
        const lastCommaIndex = standardizedAmount.lastIndexOf(',');

        if (lastDotIndex > lastCommaIndex) {
            // The dot is the decimal separator
            // Remove all commas used as thousand separators
            standardizedAmount = standardizedAmount.replace(/,/g, '');
        } else if (lastCommaIndex > lastDotIndex) {
            // The comma is the decimal separator
            // Remove all dots used as thousand separators
            standardizedAmount = standardizedAmount.replace(/\./g, '');
            // Convert comma to dot as the decimal separator
            standardizedAmount = standardizedAmount.replace(/,/g, '.');
        }

        // Check if the amount has at most two decimal places
        const regex = /^\d+(\.\d{1,2})?$/;
        return regex.test(standardizedAmount);
    };

    const formatMoneyForDisplay = (amount, format = moneyFormat) => {
        // PURPOSE
        // Takes the money from the DB and formats it for user display

        // ARGUMENTS
        // **Amount** - The standardized money format stored in the DB, follows 1000.99 or 1000
        // Is an actual number, not a string, and can only use periods for the decimal.
        // **Format** - Is an object with two keys at this time, decimal and separator.

        // OUTPUT
        // This function returns a string formatted per the user's preferences

        // Destructure the format object
        const { decimal, separator } = format.value;
        // Decimal: '.' | ','
        // Separator: ',' | '.' | ''(nothing) | ' '(space)

        // If the amount is not a number, return an empty string
        if (typeof amount !== 'number') {
            return '';
        }

        // Convert the amount to a string
        const amountString = amount.toString();

        // Split the amount string into an array. Using decimal because that is what is stored.
        const amountArray = amountString.split('.');
        const mainString = amountArray[0];
        let decimalString = amountArray[1];

        // Let's deal with the number before the decimal first, called "main" in this function

        // We use regex to insert the separator at the appropriate places
        // 1 -> 1
        // 100 => 100
        // 1000 => 1,000
        // 1000000 => 1,000,000
        const regex = /\B(?=(\d{3})+(?!\d))/g;
        const mainWithUserFormat = mainString.replace(regex, separator);

        // Value we are returning
        let outputString = mainWithUserFormat;

        // Moving on to the decimal

        // Add decimals to the output string
        // For now forcing at least 2 (allowing for more for currencies that have more)
        decimalString = _.padEnd(decimalString, 2, '0');
        outputString += decimal + decimalString;

        // Return the formatted amount
        return outputString;
    };

    const formatMoneyStandard = (amountInput) => {
        // PURPOSE
        // Takes the money amount typed by the user and standardizes it for save

        // ARGUMENTS
        // **amountInput** - The user typed input, a STRING.

        // OUTPUT
        // This function returns a NUMBER that has a standard format for storing in the DB
        // and doing processes on it like sort, filter, and calculations (calculated fields).
        // Returns null if the input is not a valid number, such as an empty string.

        // CAUTION
        // Input could be in any format, not necessarily the format in the settings.
        // Also different countries have different formats. Someone could 5,5 or 5.5, and there
        // are currencies that have more than 3 decimal places (lookin' at you, Dinar).

        const standardizedAmount = sanitizeAmount(amountInput);
        const parsedAmount = parseFloat(standardizedAmount);

        // Parse the amount to a float or null
        const formattedAmount = _.isFinite(parsedAmount)
            ? parsedAmount
            : null;

        return formattedAmount;
    };

    return {
        checkForValidChars,
        checkForValidFormat,
        formatMoneyForDisplay,
        formatMoneyStandard,
    };
}

/*
 * An extractor function that allows the use of specialized syntax to specify
 * a range of selectors that should not be purged.
 *
 * If you are dynamically building a css selector you can add a comment in the
 * same file that specifies all the possible selectors that can be created from
 * that file.
 *
 * All comments must be preceded with "@no-purge".
 *
 * For example, a file that builds the selectors .bg-red-100 and
 * .border-green-400 can be read by this extractor if the string
 * {bg|border}-{red|green}-{1|4}00 is included in a comment. This will in fact,
 * cover all possible combinations of the strings wrapped in curly braces. So
 * this would match .border-green-100, .border-red-100, .border-red-400,
 * .bg-red-400, .bg-green-100, and .bg-green-400.
 * If you only wanted the purge to spot the two selectors specified above you
 * could use the syntax {bg-red-100|border-green-400}.
 *
 * To figure out how many selectors you are allowing you can multiply the number
 * of terms within each curly brace. So in the first example there are 2 terms
 * in each of the 3 sections which makes 8 possible selectors. In the second
 * example there are only 2 terms in 1 section which means 2 possible selectors.
 *
 * Alternatively you can use the syntax {:color} and {:intensity} to include all
 * colors and all intensities for that selector but be careful this will end up
 * including a lot of CSS.
 */

const { uniq } = require('lodash');

// Build an array of all the permutations of the provided arrays.
// For example an argument of [[1, 2], [3, 4]] should return:
// [[1, 3], [2, 3], [1, 4], [2, 4]]
function permutations(matches, group = [], build = []) {
    const bottom = matches.length === 1;
    // In our example remaining matches would be [[3, 4]]
    const remainingMatches = bottom ? null : matches.slice(1);
    // Combined matches would start as an empty array
    let combinedMatches = build;
    // Here we loop through the first array and recursively call this function
    for (let i = 0; i < matches[0].length; i += 1) {
        // For the first call newGroup would be [1] and [2] and that would be
        // passed to the function again with the remaining matches which are
        // pushed onto the end.
        const newGroup = [...group, matches[0][i]];
        if (bottom) {
            combinedMatches.push(newGroup);
        } else {
            combinedMatches = permutations(remainingMatches, newGroup, combinedMatches);
        }
    }
    return combinedMatches;
}

module.exports = function noPurgeExtractor(content, colors, intensities) {
    // First we get all lines that start with @no-purge as well as lines that
    // look like they might have some sort of dynamic syntax
    let matches = content.match(/(?<=@no-purge )(.*)/g) || [];
    // This matches things like bg-${color}-${intensity} or text-${color} etc
    matches = matches.concat(content.match(/\b[\w-]+?\${[^}]+}(-\${[^}]+})?/g) || []);

    return uniq(matches).flatMap((match) => {
        // Extract the values in {} and turn them into arrays\$?.
        const wildcards = match.match(/(?<=\$?{)[^}]*/g).map((m) => {
            if (/:?color/.test(m)) {
                return colors;
            } if (/:?intensity/.test(m)) {
                return intensities;
            }
            return m.split('|');
        });
        // Merge all the wildcards into an array of permutations.
        const possibilities = permutations(wildcards);

        // Loop through all the permutations and generate the CSS class that
        // should not be purged.
        return possibilities.map((replacements) => {
            let i = 0;
            return match.replace(/\$?{[^}]*}/g, () => {
                const replace = replacements[i];
                i += 1;
                return replace;
            });
        });
    });
};

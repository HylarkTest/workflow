export default function rangePlugin(option, dayjsClass, dayjsFactory) {
    /*
     * A method that creates an array of dayjs objects between the provided
     * start date and end date.
     *
     * The behaviour can be configured with an optional third `options` object:
     * options = {
     *     // Possible values are any dayjs unit that specifies the difference
     *     // between each date object
     *     unit: 'day'
     *     // The number of units between each object
     *     interval: 1
     *     // Possible values are 'start', 'end', and 'both' indicating which
     *     //of the start and end dates to include in the final array
     *     inclusive: 'both'
     * }
     */
    // eslint-disable-next-line no-param-reassign
    dayjsFactory.prototype.range = function range(startDate, endDate, options = {}) {
        const {
            unit = 'day',
            interval = 1,
            inclusive = 'both',
        } = (options || {});

        const units = `${unit}s`;
        const includeStart = inclusive === 'both' || inclusive === 'start';
        const includeEnd = inclusive === 'both' || inclusive === 'end';

        const dateRange = [];
        for (
            let i = includeStart ? startDate.clone() : startDate.add(interval, units);
            i.isBefore(endDate, unit) || (includeEnd && i.isSame(endDate, unit));
            i = i.add(interval, units)
        ) {
            dateRange.push(i);
        }

        return dateRange;
    };
}

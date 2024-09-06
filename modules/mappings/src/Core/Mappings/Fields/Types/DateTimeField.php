<?php

declare(strict_types=1);

namespace Mappings\Core\Mappings\Fields\Types;

use Illuminate\Support\Carbon;
use Mappings\Core\Timestamps\DateTimeStringFormat;

class DateTimeField extends DateField
{
    public static string $type = 'DATE_TIME';

    public string $graphQLType = 'DateTime';

    public string $graphQLInputType = 'DateTime';

    protected function maxDifferenceInSeconds(): ?int
    {
        return $this->rule('maxDifference');
    }

    /**
     * @param  \Illuminate\Support\Carbon  $value
     * @param  \Illuminate\Support\Carbon|null  $originalValue
     */
    protected function serializeSinglePart($value, $originalValue = null): string
    {
        return $value->toDateTimeString();
    }

    /**
     * @param  array  $args
     * @param  string|null  $value
     * @return string
     */
    protected function resolveIndividualValue($value, $args)
    {
        $format = DateTimeStringFormat::from($args['format'] ?? 'DATETIME')->dateTimeFormat();

        return Carbon::parse($value)->timezone($args['timezone'] ?? 'UTC')->format($format);
    }

    protected function arguments(): ?array
    {
        return [
            'timezone' => $this->buildGraphQLType('TimeZone', default: 'UTC'),
            'format' => $this->buildGraphQLType('DateTimeStringFormat', default: 'DATETIME'),
        ];
    }
}

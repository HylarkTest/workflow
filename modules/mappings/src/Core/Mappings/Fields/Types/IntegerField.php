<?php

declare(strict_types=1);

namespace Mappings\Core\Mappings\Fields\Types;

use Mappings\Core\Mappings\Fields\Field;
use Mappings\Core\Mappings\Fields\Contracts\RangeField;
use Mappings\Core\Mappings\Fields\Concerns\HasRangeOption;

class IntegerField extends Field implements RangeField
{
    use HasRangeOption;

    public static string $type = 'INTEGER';

    public string $graphQLType = 'Int';

    public string $graphQLInputType = 'Int';

    public function messages(): array
    {
        $messages = parent::messages();
        $messages['to.after'] = $this->translator->get(
            'validation.after',
            ['attribute' => "\"$this->name\" to", 'date' => "\"$this->name\" from"]
        );

        return $messages;
    }
}

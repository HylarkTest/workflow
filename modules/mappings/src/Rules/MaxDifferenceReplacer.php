<?php

declare(strict_types=1);

namespace Mappings\Rules;

use Carbon\CarbonInterval;
use Illuminate\Support\Carbon;
use Illuminate\Validation\Validator;
use Illuminate\Contracts\Translation\Translator;

class MaxDifferenceReplacer
{
    protected Translator $translator;

    protected Validator $validator;

    public function __construct(Translator $translator)
    {
        $this->translator = $translator;
    }

    /**
     * @param  array<int, mixed>  $parameters
     *
     * @throws \Exception
     */
    public function replace(string $message, string $attribute, string $rule, array $parameters, Validator $validator): string
    {
        $this->validator = $validator;

        $difference = (new CarbonInterval(0))->seconds($parameters[1])->invert()->cascade()->forHumans([
            'parts' => 6,
            'options' => Carbon::SEQUENTIAL_PARTS_ONLY,
            'join' => true,
            'syntax' => Carbon::DIFF_RELATIVE_AUTO,
        ]);

        $message = str_replace(':difference', $difference, $message);

        if (! strtotime($parameters[0])) {
            return str_replace(':date', $validator->getDisplayableAttribute($parameters[0]), $message);
        }

        return str_replace(':date', $validator->getDisplayableValue($attribute, $parameters[0]), $message);
    }
}

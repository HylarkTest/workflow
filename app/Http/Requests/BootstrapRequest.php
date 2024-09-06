<?php

declare(strict_types=1);

namespace App\Http\Requests;

class BootstrapRequest extends BaseRequest
{
    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        $rules = $this->baseRules();

        // The request expects an array of base configurations,
        // but instead of adding *. to every key above, it is easier to add
        // them all here.
        $rules = collect($rules)
            ->mapWithKeys(fn ($rule, $key) => ["*.$key" => $rule])
            ->all();

        foreach ($this->input() as $baseKey => $baseData) {
            $this->addFieldRules($rules, $baseData, "{$baseKey}.");
        }

        return $rules;
    }
}

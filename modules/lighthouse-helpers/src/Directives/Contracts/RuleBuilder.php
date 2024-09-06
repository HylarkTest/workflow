<?php

declare(strict_types=1);

namespace LighthouseHelpers\Directives\Contracts;

interface RuleBuilder
{
    public function rules(array $data): array;

    public function messages(): ?array;
}

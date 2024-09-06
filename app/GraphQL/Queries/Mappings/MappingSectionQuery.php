<?php

declare(strict_types=1);

namespace App\GraphQL\Queries\Mappings;

use App\GraphQL\AppContext;
use Illuminate\Support\Arr;
use LighthouseHelpers\Core\Mutation;
use GraphQL\Type\Definition\ResolveInfo;
use Illuminate\Contracts\Validation\Factory;
use Mappings\Core\Mappings\Sections\Section;
use Illuminate\Contracts\Translation\Translator;

class MappingSectionQuery extends Mutation
{
    protected Translator $translator;

    public function __construct(Factory $validationFactory, Translator $translator)
    {
        parent::__construct($validationFactory);
        $this->translator = $translator;
    }

    /**
     * @param  null  $rootValue
     */
    public function store($rootValue, array $args, AppContext $context, ResolveInfo $resolveInfo): array
    {
        $base = $context->base();

        /** @var \App\Models\Mapping $mapping */
        $mapping = $base->mappings()->findOrFail($args['input']['mappingId']);

        $data = $this->validateSection($args, $resolveInfo);

        $mapping->addSection($data['input']);

        return $this->mutationResponse(200, 'Mapping section created successfully', [
            'mapping' => $mapping,
        ]);
    }

    /**
     * @param  null  $rootValue
     */
    public function destroy($rootValue, array $args, AppContext $context): array
    {
        $base = $context->base();

        /** @var \App\Models\Mapping $mapping */
        $mapping = $base->mappings()->findOrFail($args['input']['mappingId']);

        $mapping->removeSection($args['input']['id']);

        return $this->mutationResponse(200, 'Mapping section deleted successfully', [
            'mapping' => $mapping,
        ]);
    }

    /**
     * @param  null  $rootValue
     */
    public function update($rootValue, array $args, AppContext $context, ResolveInfo $resolveInfo): array
    {
        $base = $context->base();

        /** @var \App\Models\Mapping $mapping */
        $mapping = $base->mappings()->findOrFail($args['input']['mappingId']);

        $data = $this->validateSection($args, $resolveInfo);

        $mapping->updateSection($args['input']['id'], $data['input']);

        return $this->mutationResponse(200, 'Mapping section updated successfully', [
            'mapping' => $mapping,
        ]);
    }

    protected function validateSection(array $args, ResolveInfo $resolveInfo): array
    {
        return $this->validate(
            $args,
            ['input.name' => 'string|required|max:'.Section::MAX_LENGTH],
            $resolveInfo,
            [],
            Arr::dot($this->translator->get('mappings::validation.attributes'))
        );
    }
}

<?php

declare(strict_types=1);

namespace App\Core\Mappings\Features;

use App\Models\Mapping;
use LighthouseHelpers\Utils;

class CollaborationFeature extends Feature
{
    public MappingFeatureType $val = MappingFeatureType::COLLABORATION;

    protected array $defaultOptions = [
        'roles' => [
            ['name' => 'Lead'],
            ['name' => 'Collaborator'],
        ],
    ];

    public function __construct(protected Mapping $parent, ?array $options = null)
    {
        parent::__construct($parent, $options);

        $this->options['roles'] = array_map(static function (array $role) {
            if (! isset($role['id'])) {
                $role['id'] = Utils::generateRandomString();
            }

            return $role;
        }, $this->options['roles'] ?? []);
    }
}

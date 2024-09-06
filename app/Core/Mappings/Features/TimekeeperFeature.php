<?php

declare(strict_types=1);

namespace App\Core\Mappings\Features;

use App\Models\Mapping;
use LighthouseHelpers\Utils;

class TimekeeperFeature extends Feature
{
    protected MappingFeatureType $val = MappingFeatureType::TIMEKEEPER;

    protected array $defaultOptions = [
        'dates' => [
            ['name' => 'Start date'],
            ['name' => 'End date'],
        ],
    ];

    public function __construct(protected Mapping $parent, ?array $options = null)
    {
        parent::__construct($parent, $options);

        $this->options['dates'] = array_map(static function (array $role) {
            if (! isset($role['id'])) {
                $role['id'] = Utils::generateRandomString();
            }

            return $role;
        }, $this->options['dates'] ?? []);
    }
}

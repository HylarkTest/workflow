<?php

declare(strict_types=1);

namespace App\Core;

use Illuminate\Support\Collection;

/**
 * @phpstan-type PlanFeature array{
 *     feature: string,
 *     description: string,
 *     core: bool|float|int|string,
 *     ascend: bool|float|int|string,
 *     soar: bool|float|int|string,
 * }
 */
class PlanFeatureRepository
{
    /**
     * @var \Illuminate\Support\Collection<string, PlanFeature>
     */
    protected Collection $planFeatures;

    /**
     * @return \Illuminate\Support\Collection<string, PlanFeature>
     */
    public function getPlanFeatures(): Collection
    {
        return $this->loadPlanFeatures();
    }

    /**
     * @return PlanFeature
     */
    public function getPlanFeature(string $feature)
    {
        $planFeature = $this->loadPlanFeatures()->get($feature);
        if ($planFeature === null) {
            throw new \InvalidArgumentException("Invalid plan feature: $feature");
        }

        return $planFeature;
    }

    public function getFeatureLimit(string $feature, string $plan): int|float|bool|string
    {
        $planFeature = $this->getPlanFeature($feature);

        return $planFeature[$plan];
    }

    /**
     * @return \Illuminate\Support\Collection<string, PlanFeature>
     */
    protected function loadPlanFeatures(): Collection
    {
        if (isset($this->planFeatures)) {
            return $this->planFeatures;
        }
        $csvFile = database_path('plans.csv');
        $csv = file_get_contents($csvFile);
        if ($csv === false) {
            throw new \RuntimeException("Unable to read file: $csvFile");
        }
        $lines = explode("\n", $csv);
        /** @var string[] $headers */
        $headers = str_getcsv(array_shift($lines));
        $this->planFeatures = collect();
        foreach ($lines as $line) {
            if (! $line) {
                continue;
            }
            $row = str_getcsv($line);
            $count = \count($row);
            for ($i = 2; $i < $count; $i++) {
                $row[$i] = $this->formatValue((string) $row[$i]);
            }
            /** @var PlanFeature $planFeature */
            $planFeature = array_combine($headers, $row);
            $this->planFeatures[$planFeature['feature']] = $planFeature;
        }

        return $this->planFeatures;
    }

    protected function formatValue(string $value): int|float|bool|string
    {
        if (is_numeric($value)) {
            return (int) $value;
        }
        if (\in_array($value, ['true', 'false'], true)) {
            return $value === 'true';
        }
        if ($value === 'INF') {
            return \INF;
        }

        return $value;
    }
}

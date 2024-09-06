<?php

declare(strict_types=1);

namespace App\Console\Commands;

use Illuminate\Support\Str;
use Illuminate\Console\Command;

class PricingJsonBuildCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'pricing:json:build';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Build pricing JSON file with description strings';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $config = config('pricing');

        $config = $this->camelKeys($config);

        foreach ($config['planSummaries'] as &$summary) {
            $summary['features'] = array_map([$this, 'translate'], $summary['features']);
            if ($summary['pendingFeatures'] ?? null) {
                $summary['pendingFeatures'] = array_map([$this, 'translate'], $summary['pendingFeatures']);
            }
        }

        foreach ($config['featureSections'] as $section => &$info) {
            $info['title'] = __("*.landing.pricing.full.sections.$section.title");
            foreach ($info['features'] as $feature => &$featureInfo) {
                foreach ($featureInfo['plans'] as $plan => &$limit) {
                    if (isset($limit['text'])) {
                        $limit['text'] = $this->translate($limit['text']);
                    }
                    if ($limit['value'] === \INF) {
                        $limit['value'] = 'INF';
                    }
                }
                $featureInfo['title'] = $this->translate("*.landing.pricing.full.sections.$section.$feature.title");
                $featureInfo['explanation'] = $this->translate("*.landing.pricing.full.sections.$section.$feature.explanation");
            }
        }
        $this->info(json_encode($config, \JSON_PRETTY_PRINT | \JSON_THROW_ON_ERROR));

        return 0;
    }

    protected function translate(string|array $key): string
    {
        $translationKey = \is_string($key) ? $key : $key[0];

        if (isset($key[2])) {
            /** @var string $translation */
            $translation = trans_choice($translationKey, $key[2]);
        } else {
            /** @var string $translation */
            $translation = __($translationKey);
        }

        if (\is_array($key) && isset($key[1])) {
            $shouldReplace = [];

            foreach ($key[1] as $replace => $value) {
                $shouldReplace['{'.$replace.'}'] = $value;
            }
            $translation = strtr($translation, $shouldReplace);
        }

        return $translation;
    }

    protected function camelKeys(array &$arr): array
    {
        foreach ($arr as $key => $value) {
            if (\is_string($key)) {
                unset($arr[$key]);
                $arr[Str::camel($key)] = \is_array($value) ? $this->camelKeys($value) : $value;
            }
        }

        return $arr;
    }
}

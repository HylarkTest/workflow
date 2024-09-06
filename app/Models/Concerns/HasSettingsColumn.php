<?php

declare(strict_types=1);

namespace App\Models\Concerns;

use Illuminate\Database\Eloquent\Casts\Attribute;

/**
 * @template SettingsClass of \Illuminate\Contracts\Support\Arrayable
 *
 * @mixin \Illuminate\Database\Eloquent\Model
 *
 * @property SettingsClass $settings
 */
trait HasSettingsColumn
{
    /**
     * @param  \Closure(SettingsClass): void  $callback
     */
    public function updatePreferences(\Closure $callback): void
    {
        $preferences = $this->settings;

        $callback($preferences);

        $this->settings = $preferences->toArray();
        $this->save();
    }

    protected function getSettingsColumnName(): string
    {
        return property_exists($this, 'settingsColumnName') ? $this->settingsColumnName : 'settings';
    }

    /**
     * @return class-string<SettingsClass>
     */
    protected function getSettingsClass()
    {
        if (! property_exists($this, 'settingsClass')) {
            throw new \LogicException('You must define the settingsClass property on '.static::class);
        }

        return $this->settingsClass;
    }

    /**
     * @return \Illuminate\Database\Eloquent\Casts\Attribute<SettingsClass, SettingsClass|array>
     */
    protected function settings(): Attribute
    {
        return Attribute::make(
            get: function (mixed $value, array $attributes) {
                $settings = $attributes[$this->getSettingsColumnName()] ?? [];
                if (\is_string($settings)) {
                    $settings = json_decode($settings, true, 512, \JSON_THROW_ON_ERROR);
                }

                return new ($this->getSettingsClass())($settings);
            },
            /**
             * @param  SettingsClass|array  $settings
             */
            set: function (mixed $settings) {
                $settings = \is_array($settings) ? $settings : $settings->toArray();
                $defaults = (new ($this->getSettingsClass()))->toArray();

                $newSettings = [];
                foreach ($defaults as $key => $default) {
                    if (\array_key_exists($key, $settings) && $default !== $settings[$key]) {
                        $newSettings[$key] = $settings[$key];
                    }
                }

                return [$this->getSettingsColumnName() => json_encode($newSettings, \JSON_THROW_ON_ERROR)];
            },
        );
    }

    /**
     * @return \Illuminate\Database\Eloquent\Casts\Attribute<array, array>
     */
    protected function settingsArray(): Attribute
    {
        return Attribute::get(function () {
            return $this->settings->toArray();
        });
    }
}

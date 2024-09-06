<?php

declare(strict_types=1);

namespace LaravelUtils\Enums;

use Illuminate\Support\Str;
use Illuminate\Support\Facades\Lang;

trait AdvancedEnum
{
    /**
     * Get the description for an enum value.
     */
    public function getDescription(): string
    {
        return
            $this->getLocalizedDescription() ??
            $this->getFriendlyKeyName();
    }

    public static function coerce($enumKeyOrValue): static
    {
        if ($enumKeyOrValue instanceof static) {
            return $enumKeyOrValue;
        }

        if (static::hasValue($enumKeyOrValue)) {
            return static::from($enumKeyOrValue);
        }

        throw new \InvalidArgumentException('Enum could not be created from the argument');
    }

    public static function hasValue($value): bool
    {
        $cases = static::cases();
        foreach ($cases as $case) {
            if ($value === $case->value) {
                return true;
            }
        }

        return false;
    }

    /**
     * Get the default localization key.
     */
    public static function getLocalizationKey(): string
    {
        return 'enums.'.static::class;
    }

    /**
     * Get the localized description of a value.
     *
     * This works only if localization is enabled
     * for the enum and if the key exists in the lang file.
     */
    protected function getLocalizedDescription(): ?string
    {
        $localizedStringKey = static::getLocalizationKey().'.'.$this->value;

        if (Lang::has($localizedStringKey)) {
            return __($localizedStringKey);
        }

        return null;
    }

    /**
     * Transform the key name into a friendly, formatted version.
     */
    protected function getFriendlyKeyName(): string
    {
        $key = $this->value;
        if (ctype_upper(preg_replace('/[^a-zA-Z]/', '', $key))) {
            $key = mb_strtolower($key);
        }

        return ucfirst(str_replace('_', ' ', Str::snake($key)));
    }
}

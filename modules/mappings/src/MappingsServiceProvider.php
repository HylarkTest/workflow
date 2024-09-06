<?php

declare(strict_types=1);

namespace Mappings;

use BenSampo\Enum\Enum;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Validation\Validator;
use Mappings\Rules\MaxDifferenceRule;
use BenSampo\Enum\EnumServiceProvider;
use Illuminate\Support\ServiceProvider;
use Mappings\Core\Mappings\MappingType;
use Mappings\Rules\MaxDifferenceReplacer;
use LighthouseHelpers\Core\NativeEnumType;
use Mews\Purifier\PurifierServiceProvider;
use Nuwave\Lighthouse\Schema\TypeRegistry;
use Tests\Mappings\MockCurrencyRepository;
use Mappings\Core\Mappings\Fields\FieldType;
use LighthouseHelpers\GraphQLServiceProvider;
use Mappings\Core\Mappings\Fields\SalaryPeriod;
use Illuminate\Contracts\Translation\Translator;
use LighthouseHelpers\Core\DynamicLaravelEnumType;
use Mappings\Core\Timestamps\DateTimeStringFormat;
use Mappings\Core\Currency\FixerCurrencyRepository;
use Mappings\Core\Mappings\Fields\AddressFieldName;
use Mappings\Core\Documents\EloquentImageRepository;
use Mappings\Core\Currency\DatabaseCurrencyRepository;
use Mappings\Core\Documents\Contracts\ImageRepository;
use Mappings\Core\Documents\EloquentDocumentRepository;
use Mappings\Core\Currency\Contracts\CurrencyRepository;
use Mappings\Core\Documents\Contracts\DocumentRepository;
use Nuwave\Lighthouse\Events\RegisterDirectiveNamespaces;
use Mappings\Core\Mappings\Relationships\RelationshipType;
use Illuminate\Contracts\Validation\Factory as ValidatorFactory;

class MappingsServiceProvider extends ServiceProvider
{
    /**
     * @var array<class-string, class-string>
     */
    public array $singletons = [
        DocumentRepository::class => EloquentDocumentRepository::class,
        ImageRepository::class => EloquentImageRepository::class,
    ];

    /**
     * @var class-string[]
     */
    public static array $enums = [
        FieldType::class,
        MappingType::class,
        RelationshipType::class,
        AddressFieldName::class,
        SalaryPeriod::class,
        DateTimeStringFormat::class,
    ];

    public function boot(TypeRegistry $typeRegistry, ValidatorFactory $validator, Translator $translator): void
    {
        $this->loadResources();

        $this->bootCurrencyProvider();

        $this->bootGraphQl($typeRegistry);

        $validator->extend(
            'max_difference',
            MaxDifferenceRule::class,
            $translator->get('mappings::validation.rules.max_difference')
        );

        $validator->replacer(
            'max_difference',
            MaxDifferenceReplacer::class
        );

        $validator->extend(
            'max_strip_format',
            function (string $attribute, $value, array $parameters, Validator $validator) {
                /** @var \MarkupUtils\MarkupType $format */
                $format = config('mappings.fields.formatted.format');
                $markup = $format->createMarkup($value);

                return $validator->validateMax($attribute, (string) $markup->convertToPlaintext(), $parameters);
            },
            $translator->get('validation.max.string')
        );

        $validator->replacer(
            'max_strip_format',
            fn (string $message, string $attribute, string $rule, array $parameters, Validator $validator) => str_replace(':max', $parameters[0], $message)
        );

        $validator->extend(
            'lte_default',
            function (string $attribute, $value, array $parameters, Validator $validator) {
                $validator->requireParameterCount(2, $parameters, 'lte_default');
                $hasAttribute = Arr::has($validator->getData(), $parameters[0]);

                return $validator->validateLte($attribute, $hasAttribute ? $parameters[0] : $parameters[1], $parameters);
            },
            $translator->get('validation.lte.numeric')
        );

        $validator->replacer(
            'lte_default',
            static function (string $message, string $attribute, string $rule, array $parameters, Validator $validator) {
                $hasAttribute = Arr::has($validator->getData(), $parameters[0]);
                $replace = $hasAttribute ? $validator->getTranslator()->get("mappings::validation.attributes.{$parameters[0]}") : $parameters[1];

                return str_replace(':value', $replace, $message);
            }
        );
    }

    public function register()
    {
        $this->app->register(GraphQLServiceProvider::class);
        $this->app->register(EnumServiceProvider::class);
        $this->app->register(PurifierServiceProvider::class);

        $fieldTypes = scandir(__DIR__.'/Core/Mappings/Fields/Types');
        if ($fieldTypes) {
            $files = Collection::make($fieldTypes)
                ->filter(fn (string|false $file): bool => $file && $file[0] !== '.');

            /** @var array<class-string<\Mappings\Core\Mappings\Fields\Field>> $fieldClasses */
            $fieldClasses = $files->map(fn (string $file): string => '\\Mappings\\Core\\Mappings\\Fields\\Types\\'.mb_substr($file, 0, -4))
                ->all();

            FieldType::registerFields($fieldClasses);
        }

        // TODO: Extract to manager
        match (config('mappings.currencies.driver')) {
            'database' => $this->app->singleton(CurrencyRepository::class, DatabaseCurrencyRepository::class),
            'fixer' => $this->app->singleton(CurrencyRepository::class, FixerCurrencyRepository::class),
            default => $this->app->singleton(CurrencyRepository::class, MockCurrencyRepository::class),
        };
    }

    protected function loadResources(): void
    {
        $this->publishes([
            __DIR__.'/../config/mappings.php' => config_path('mappings.php'),
            __DIR__.'/../lang' => resource_path('lang/vendor/mappings'),
            __DIR__.'/../database/migrations' => database_path('migrations'),
        ]);

        $this->mergeConfigFrom(__DIR__.'/../config/mappings.php', 'mappings');

        $this->loadMigrationsFrom(__DIR__.'/../database/migrations/');

        $this->loadTranslationsFrom(__DIR__.'/../lang', 'mappings');
    }

    protected function bootCurrencyProvider(): void
    {
        $this->app->when(FixerCurrencyRepository::class)
            ->needs('$accessKey')
            ->give(config('mappings.currencies.fixer.key'));
    }

    protected function bootGraphQl(TypeRegistry $typeRegistry): void
    {
        foreach (static::$enums as $enum) {
            if (\is_string($enum) && is_subclass_of($enum, Enum::class)) {
                $typeRegistry->register(new DynamicLaravelEnumType($enum));
            } else {
                $typeRegistry->register(new NativeEnumType($enum));
            }
        }

        $config = $this->app->make('config');
        $events = $this->app->make('events');

        $modelNamespaces = (array) $config->get('lighthouse.namespaces.models');
        $modelNamespaces[] = 'Mappings\\Models';
        $config->set('lighthouse.namespaces.models', $modelNamespaces);

        $events->listen(
            RegisterDirectiveNamespaces::class,
            fn () => 'Mappings\\GraphQL\\Directives'
        );
    }
}

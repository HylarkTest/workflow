<?php

declare(strict_types=1);

namespace App\Nova;

use Illuminate\Support\Str;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Date;
use Laravel\Nova\Fields\Text;
use App\Core\Account\AdminRole;
use App\Nova\Lenses\NovaAdmins;
use Laravel\Nova\Fields\Avatar;
use Illuminate\Validation\Rules;
use Laravel\Nova\Fields\Boolean;
use Laravel\Nova\Fields\Password;
use Laravel\Nova\Fields\MorphMany;
use Illuminate\Support\Facades\Redis;
use Laravel\Nova\Fields\BooleanGroup;
use App\Nova\Lenses\MostValuableUsers;
use Illuminate\Support\Facades\Storage;
use App\Nova\Columns\SearchableGlobalId;
use Laravel\Nova\Exceptions\NovaException;
use Laravel\Nova\Http\Requests\NovaRequest;

/**
 * @mixin \App\Models\User
 *
 * @extends \App\Nova\Resource<\App\Models\User>
 */
class User extends Resource
{
    /**
     * The model the resource corresponds to.
     *
     * @var string
     */
    public static $model = \App\Models\User::class;

    /**
     * The single value that should be used to represent
     * the resource when being displayed.
     *
     * @var string
     */
    public static $title = 'name';

    public static $with = ['bases.subscriptions'];

    public static function searchableColumns(): array
    {
        return [
            'id',
            new SearchableGlobalId,
            'name',
            'email',
        ];
    }

    /**
     * Get the fields displayed by the resource.
     *
     * @param  NovaRequest  $request  *
     */
    public function fields(NovaRequest $request): array
    {
        $roleField = BooleanGroup::make('Role', 'admin_role')
            ->options([
                AdminRole::MANAGER()->value => 'Manager: '.AdminRole::getDescription(AdminRole::MANAGER),
                AdminRole::KNOWLEDGE_BASE_AGENT()->value => 'Knowledge base agent: '.AdminRole::getDescription(AdminRole::KNOWLEDGE_BASE_AGENT),
                AdminRole::SUPPORT()->value => 'Support: '.AdminRole::getDescription(AdminRole::SUPPORT),
            ])
            ->resolveUsing(fn (?AdminRole $role) => collect(AdminRole::getInstances())->mapWithKeys(fn (AdminRole $instance) => [
                $instance->value => $this->hasAdminEmail() || ($role?->hasFlag($instance) ?: false),
            ]))
            ->fillUsing(function (NovaRequest $request, $model, string $attribute, string $requestAttribute) {
                /** @var \App\Models\User $user */
                $user = $request->user();
                if (! $user->can('updateAdminRole', $model)) {
                    throw new NovaException('You are not authorized to perform this action.');
                }
                if ($request->exists($requestAttribute)) {
                    /** @var array<int, bool> $role */
                    $role = json_decode($request[$requestAttribute], true);
                    $flags = collect($role)->filter()->keys()->map(fn ($value) => AdminRole::fromValue($value))->toArray();
                    $model->{$attribute} = $flags ? AdminRole::flags($flags) : null;
                }
            })
            ->readonly(fn () => $this->hasAdminEmail())
            ->canSeeWhen('updateAdminRole', $this)
            ->hideFromIndex()
            ->nullable();

        if ($this->hasAdminEmail()) {
            $roleField->help('Admin role cannot be updated when the user\'s email is in the config file.');
        }

        return [
            ID::make()->sortable(),

            Text::make('Role', 'admin_role')
                ->resolveUsing(fn (?AdminRole $role) => match (true) {
                    ! $role => null,
                    $role->isSuperAdmin() => 'Super Admin',
                    default => collect($role->getFlags())->map(fn (AdminRole $role) => Str::title($role->key))->join(', '),
                })
                ->nullable()
                ->onlyOnIndex(),

            $roleField,

            Avatar::make('Avatar')->disk('images')
                ->exceptOnForms()
                ->resolveUsing(fn ($url) => $url
                    ? (str_starts_with($url, 'base') ? $url : "base{$this->firstPersonalBase()->id}/$url")
                    : null)
                ->preview(fn ($value, $disk) => $value
                    ? Storage::disk($disk)->url($value)
                    : sprintf('/images/defaultPeople/person%d.png', ($this->id % 10) + 1)
                ),

            Text::make('Name', 'name')
                ->sortable()
                ->rules('required', 'max:255'),

            Text::make('Email')
                ->sortable()
                ->rules('required', 'email', 'max:254')
                ->creationRules('unique:users,email')
                ->updateRules('unique:users,email,{{resourceId}}'),

            Boolean::make('Registered', 'finished_registration_at')
                ->resolveUsing(fn (?string $timestamp) => $timestamp !== null)
                /** @phpstan-ignore-next-line Should match the signature */
                ->fillUsing(function (NovaRequest $request, \App\Models\User $model, string $requestAttribute) {
                    $value = $request[$requestAttribute] ? now() : null;
                    $model->finished_registration_at = $value;

                    return $value;
                })->exceptOnForms(),

            Boolean::make('Verified', 'email_verified_at')
                ->resolveUsing(fn (?string $timestamp) => $timestamp !== null)
                ->exceptOnForms(),

            Boolean::make(
                'Premium',
                function () {
                    return $this->ownsPremiumBase();
                }
            ),

            Text::make(
                'Page',
                function () {
                    if ($this->finished_registration_at) {
                        return null;
                    }
                    $page = Redis::connection(config('key-value-store.store'))
                        ->get('store:'.$this->id.':savedRegistrationPage') ?: '';

                    return Str::of($page)->afterLast('/')->before('\\')->ucfirst();
                }
            ),

            Date::make('Created', 'created_at')
                ->sortable()
                ->exceptOnForms(),

            Password::make('Password')
                ->onlyOnForms()
                ->creationRules('required', Rules\Password::defaults())
                ->updateRules('nullable', Rules\Password::defaults()),

            MorphMany::make('Subscriptions'),
        ];
    }

    /**
     * Get the cards available for the resource.
     *
     * @param  NovaRequest  $request  *
     */
    public function cards(NovaRequest $request): array
    {
        return [];
    }

    /**
     * Get the filters available for the resource.
     *
     * @param  NovaRequest  $request  *
     */
    public function filters(NovaRequest $request): array
    {
        return [
            new Filters\UserState,
        ];
    }

    /**
     * Get the lenses available for the resource.
     *
     * @param  NovaRequest  $request  *
     */
    public function lenses(NovaRequest $request): array
    {
        return [
            new MostValuableUsers,
            new NovaAdmins,
        ];
    }

    /**
     *  Get the actions available for the resource.
     *
     * @param  NovaRequest  $request  *
     */
    public function actions(NovaRequest $request): array
    {
        return [];
    }
}

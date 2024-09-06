<?php

declare(strict_types=1);

namespace App\Nova\Lenses;

use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Lenses\Lens;
use App\Core\Account\AdminRole;
use Laravel\Nova\Fields\Avatar;
use Laravel\Nova\Fields\BooleanGroup;
use Illuminate\Support\Facades\Storage;
use Illuminate\Database\Eloquent\Builder;
use Laravel\Nova\Http\Requests\LensRequest;
use Laravel\Nova\Http\Requests\NovaRequest;

/**
 * @mixin \App\Models\User
 */
class NovaAdmins extends Lens
{
    /**
     * The columns that should be searched.
     *
     * @var array
     */
    public static $search = [];

    /**
     * Get the query builder / paginator for the lens.
     *
     * @param  \Illuminate\Database\Eloquent\Builder<\App\Models\User>  $query
     * @return mixed
     */
    public static function query(LensRequest $request, $query)
    {
        return $request->withOrdering($request->withFilters(
            $query->where(function (Builder $builder) {
                $builder->whereNotNull('admin_role')
                    ->orWhereIn('email', config('hylark.admin_emails'));
            })
        ));
    }

    /**
     * Get the fields available to the lens.
     *
     *
     * @return array
     */
    public function fields(NovaRequest $request)
    {
        return [
            ID::make()->sortable(),

            Text::make('Role', 'admin_role')
                ->resolveUsing(fn (?AdminRole $role) => match (true) {
                    $this->isSuperAdmin() => 'Super Admin',
                    ! $role => null,
                    default => collect($role->getFlags())->map(fn (AdminRole $role) => $role->description)->join(', '),
                })
                ->nullable()
                ->onlyOnIndex(),

            BooleanGroup::make('Role', 'admin_role')
                ->options([
                    AdminRole::MANAGER()->value => 'Manager: '.AdminRole::getDescription(AdminRole::MANAGER),
                    AdminRole::KNOWLEDGE_BASE_AGENT()->value => 'Knowledge Base: '.AdminRole::getDescription(AdminRole::KNOWLEDGE_BASE_AGENT),
                    AdminRole::SUPPORT()->value => 'Support: '.AdminRole::getDescription(AdminRole::SUPPORT),
                ])
                ->resolveUsing(fn (?AdminRole $role) => collect(AdminRole::getInstances())->mapWithKeys(fn (AdminRole $instance) => [
                    $instance->value => $this->hasAdminEmail() || ($role?->hasFlag($instance) ?: false),
                ]))
                ->canSeeWhen('updateAdminRole', $this)
                ->nullable(),

            Avatar::make('Avatar')->disk('images')
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
        ];
    }

    /**
     * Get the URI key for the lens.
     *
     * @return string
     */
    public function uriKey()
    {
        return 'nova-admins';
    }
}

<?php

declare(strict_types=1);

namespace App\Providers;

use App\Models\User;
use Laravel\Nova\Nova;
use App\Nova\SupportTopic;
use Laravel\Nova\Menu\Menu;
use App\Nova\SupportArticle;
use Illuminate\Http\Request;
use App\Nova\Dashboards\Main;
use App\Nova\SupportCategory;
use App\Nova\Lenses\NovaAdmins;
use Laravel\Nova\Menu\MenuItem;
use App\Nova\GlobalNotification;
use Laravel\Nova\Menu\MenuSection;
use Illuminate\Support\Facades\Gate;
use Dniccum\NovaDocumentation\NovaDocumentation;
use Laravel\Nova\NovaApplicationServiceProvider;

class NovaServiceProvider extends NovaApplicationServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        parent::boot();

        Nova::withoutNotificationCenter();

        Nova::mainMenu(function (Request $request) {
            return [
                MenuSection::dashboard(Main::class)->icon('chart-bar'),
                MenuSection::resource(\App\Nova\User::class)
                    ->icon('user')
                    ->canSee(function (Request $request) {
                        /** @var \App\Models\User $user */
                        $user = $request->user();

                        return $user->hasManagerRole() || $user->hasSupportRole();
                    }),
                MenuSection::resource(GlobalNotification::class)
                    ->icon('bell')
                    ->canSee(function (Request $request) {
                        /** @var \App\Models\User $user */
                        $user = $request->user();

                        return $user->hasSupportRole();
                    }),
                MenuSection::make('Knowledge base', [
                    MenuItem::link('Help', '/documentation/writing-articles'),
                    MenuItem::resource(SupportCategory::class),
                    MenuItem::resource(SupportArticle::class),
                    MenuItem::resource(SupportTopic::class),
                ])->icon('pencil')->collapsable()->collapsedByDefault()->canSee(function (Request $request) {
                    /** @var \App\Models\User $user */
                    $user = $request->user();

                    return $user->hasKnowledgeBaseRole();
                }),
                (new NovaDocumentation)->menu($request),
            ];
        });

        Nova::userMenu(function (Request $request, Menu $menu) {
            /** @var \App\Models\User $user */
            $user = $request->user();
            if ($user->hasManagerRole()) {
                $menu->append(MenuItem::lens(\App\Nova\User::class, NovaAdmins::class)->name('Manage admins'));
            }

            return $menu;
        });

        Nova::serving(function () {
            Nova::translations([
                'Delete Resource' => 'Delete',
                'Resource Resource' => 'Restore',
                'Force Delete Resource' => 'Force Delete',
            ]);
        });
    }

    /**
     * Get the tools that should be listed in the Nova sidebar.
     *
     * @return array
     */
    public function tools()
    {
        return [
            new NovaDocumentation,
        ];
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register() {}

    /**
     * Register the Nova routes.
     *
     * @return void
     */
    protected function routes()
    {
        Nova::routes()
            ->withAuthenticationRoutes()
            ->withPasswordResetRoutes()
            ->register();
    }

    /**
     * Register the Nova gate.
     *
     * This gate determines who can access Nova in non-local environments.
     *
     * @return void
     */
    protected function gate()
    {
        Gate::define('viewNova', function (User $user) {
            if (! app()->environment('production')) {
                return true;
            }

            return $user->isAdmin() && $user->hasEnabledTwoFactorAuthentication();
        });
    }

    /**
     * Get the dashboards that should be listed in the Nova sidebar.
     *
     * @return array
     */
    protected function dashboards()
    {
        return [
            new Main,
        ];
    }
}

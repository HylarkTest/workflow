<?php

declare(strict_types=1);

namespace App\Nova\Dashboards;

use App\Nova\Metrics\UsersPerDay;
use App\Nova\Metrics\UsersPerPage;
use App\Nova\Metrics\RegisteredUsers;
use App\Nova\Metrics\PremiumUsersPerDay;
use Laravel\Nova\Dashboards\Main as Dashboard;

class Main extends Dashboard
{
    /**
     * Get the cards for the dashboard.
     *
     * @return array
     */
    public function cards()
    {
        return [
            (new UsersPerDay)->width('1/2'),
            (new PremiumUsersPerDay)->width('1/2'),
            (new RegisteredUsers)->width('1/2'),
            (new UsersPerPage)->width('1/2'),
        ];
    }
}

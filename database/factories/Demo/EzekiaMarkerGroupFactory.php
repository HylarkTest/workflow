<?php

declare(strict_types=1);

namespace Database\Factories\Demo;

use App\Models\MarkerGroup;
use Database\Factories\MarkerGroupFactory;

class EzekiaMarkerGroupFactory extends MarkerGroupFactory
{
    public function recruitment(): self
    {
        return $this->state([
            'name' => 'Recruitment',
            'description' => 'Indications of where a person is in the recruitment pipeline.',
        ])->afterCreating(static function (MarkerGroup $group) {
            $group->markers()->createMany([
                ['name' => 'Identified', 'color' => '#81C784'],
                ['name' => 'Contacted', 'color' => '#66BB6A'],
                ['name' => 'Replied', 'color' => '#8BC34A'],
                ['name' => 'Interested', 'color' => '#388E3C'],
                ['name' => 'Phone Interview', 'color' => '#0277BD'],
                ['name' => 'Internal Interview', 'color' => '#0D47A1'],
                ['name' => 'Presented', 'color' => '#1b5e20'],
                ['name' => 'Client Interview', 'color' => '#01579B'],
                ['name' => 'Signed Off', 'color' => '#004d40'],
                ['name' => 'Not Interested', 'color' => '#e53935'],
                ['name' => 'Not Contactable', 'color' => '#424242'],
                ['name' => 'Withdrew', 'color' => '#b71c1C'],
                ['name' => 'Deleted', 'color' => '#616161'],
            ]);
        });
    }

    public function status(): self
    {
        return $this->state([
            'name' => 'Status',
            'description' => 'The current status of the item.',
        ])->afterCreating(static function (MarkerGroup $group) {
            $group->markers()->createMany([
                ['name' => 'On Target', 'color' => '#80A06E'],
                ['name' => 'Overdue', 'color' => '#f0804A'],
                ['name' => 'Urgent', 'color' => '#AA4E54'],
            ]);
        });
    }

    public function executive(): self
    {
        return $this->state([
            'name' => 'Executive',
            'description' => 'Description of executives.',
        ])->afterCreating(static function (MarkerGroup $group) {
            $group->markers()->createMany([
                ['name' => 'VIP', 'color' => '#33691E'],
                ['name' => 'Outstanding', 'color' => '#43A047'],
                ['name' => 'Strong', 'color' => '#81C784'],
                ['name' => 'Unlikely', 'color' => '#C62828'],
                ['name' => 'Good Source', 'color' => '#1E88E5'],
                ['name' => 'Client Contact', 'color' => '#FB8C00'],
            ]);
        });
    }

    public function todo(): self
    {
        return $this->state([
            'name' => 'Todo',
            'description' => 'Descriptors for types of todos.',
        ])->afterCreating(static function (MarkerGroup $group) {
            $group->markers()->createMany([
                ['name' => 'LinkedIn', 'color' => '#2196F3'],
                ['name' => 'Email', 'color' => '#3f50B5'],
                ['name' => 'Phone', 'color' => '#9c27B0'],
                ['name' => 'Meet', 'color' => '#e64a1A'],
                ['name' => 'Invite', 'color' => '#ffA727'],
                ['name' => 'Other', 'color' => '#60779B'],
            ]);
        });
    }
}

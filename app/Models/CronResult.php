<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Contracts\NotScoped;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class CronResult extends Model implements NotScoped
{
    use HasFactory;

    protected $fillable = [
        'unfinished_registrations_count',
    ];
}

<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Support\Arr;
use Jenssegers\Agent\Agent;
use Illuminate\Http\Request;
use App\Core\IPLocation\Location;
use App\Models\Contracts\NotScoped;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * Attributes
 *
 * @property int $id
 * @property string $ip
 * @property bool $succeeded
 * @property string $user_agent
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 *
 * Accessors
 * @property \Jenssegers\Agent\Agent $agent
 * @property string $browser
 * @property string $platform
 * @property string $device
 * @property string $deviceType
 *
 * Relationships
 * @property \App\Models\User $user
 */
class LoginAttempt extends Model implements NotScoped
{
    use HasFactory;

    protected $guarded = [];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<\App\Models\User, \App\Models\LoginAttempt>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Casts\Attribute<\Jenssegers\Agent\Agent, null>
     */
    public function agent(): Attribute
    {
        return Attribute::get(function (): Agent {
            $agent = new Agent;
            $agent->setUserAgent($this->user_agent);

            return $agent;
        });
    }

    /**
     * @return \Illuminate\Database\Eloquent\Casts\Attribute<string|bool, never>
     */
    public function browser(): Attribute
    {
        return Attribute::get(fn () => $this->agent->browser());
    }

    /**
     * @return \Illuminate\Database\Eloquent\Casts\Attribute<string|bool, never>
     */
    public function platform(): Attribute
    {
        return Attribute::get(fn () => $this->agent->platform());
    }

    /**
     * @return \Illuminate\Database\Eloquent\Casts\Attribute<string|bool, never>
     */
    public function device(): Attribute
    {
        return Attribute::get(fn () => $this->agent->device());
    }

    /**
     * @return \Illuminate\Database\Eloquent\Casts\Attribute<string, never>
     */
    public function deviceType(): Attribute
    {
        return Attribute::get(fn () => $this->agent->deviceType());
    }

    public function closeToRequest(Request $request): bool
    {
        $ip = Arr::last($request->ips());
        if ($this->ip === $ip) {
            return true;
        }

        $position = resolve(Location::class)->get($ip);
        if ($position) {
            return ! $position->isSuspicious
                && coord_distance((float) $position->latitude, (float) $position->longitude, (float) $this->lat, (float) $this->lon) < 250;
        }

        return false;
    }
}

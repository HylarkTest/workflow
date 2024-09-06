<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Contracts\NotScoped;
use Illuminate\Support\Facades\Storage;
use Illuminate\Database\Eloquent\Builder;
use App\Core\Preferences\NotificationChannel;
use App\Notifications\Contracts\ChannelNotification;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use LighthouseHelpers\Concerns\ConvertsCamelCaseAttributes;
use Illuminate\Notifications\DatabaseNotification as BaseDatabaseNotification;

/**
 * Attributes
 *
 * @property int $id
 * @property class-string<\Illuminate\Notifications\Notification> $type
 * @property array $data
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 *
 * Relationships
 * @property \App\Models\GlobalNotification|null $globalNotification
 */
class DatabaseNotification extends BaseDatabaseNotification implements NotScoped
{
    use ConvertsCamelCaseAttributes;

    /**
     * Indicates if the IDs are auto-incrementing.
     *
     * @var bool
     */
    public $incrementing = true;

    /**
     * The "type" of the primary key ID.
     *
     * @var string
     */
    protected $keyType = 'integer';

    protected $with = ['globalNotification'];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<\App\Models\GlobalNotification, \App\Models\DatabaseNotification>
     */
    public function globalNotification(): BelongsTo
    {
        return $this->belongsTo(GlobalNotification::class);
    }

    public function getNotificationAttribute(string $attribute, bool $translatable = true): ?string
    {
        if ($this->type === \App\Notifications\GlobalNotification::class) {
            return $this->globalNotification->data[$attribute] ?? null;
        }

        if ($translatable && $this->isLocalized($attribute)) {
            $key = $this->data[$attribute]['key'] ?? $this->data['localized'].'.'.$attribute;

            /** @var string|null $translation */
            $translation = trans($key, $this->cleanParams($this->data[$attribute]['params'] ?? []));

            return $translation === $key ? null : $translation;
        }

        return $this->data[$attribute] ?? null;
    }

    public function getHeader(): string
    {
        return (string) $this->getNotificationAttribute('header');
    }

    public function getPreview(): string
    {
        return (string) $this->getNotificationAttribute('preview');
    }

    public function getContent(): string
    {
        return (string) $this->getNotificationAttribute('content');
    }

    public function getImage(): ?string
    {
        $name = $this->getNotificationAttribute('image', false);

        return $name ? Storage::disk('public')->url($name) : $name;
    }

    public function getLink(): ?string
    {
        return $this->getNotificationAttribute('link', false);
    }

    public function getChannel(): NotificationChannel
    {
        if ($this->globalNotification) {
            return $this->globalNotification->channel;
        }

        /** @phpstan-ignore-next-line  */
        if (is_a($this->type, ChannelNotification::class)) {
            return $this->type::channel();
        }

        return NotificationChannel::ACCOUNT;
    }

    /**
     * @param  \Illuminate\Database\Eloquent\Builder<\App\Models\DatabaseNotification>  $builder
     * @param  string  $channel
     */
    public function scopeFilterChannel(Builder $builder, $channel): void
    {
        if ($channel === 'TIPS' || $channel === 'NEW_FEATURES') {
            $builder->whereHas('globalNotification', fn (Builder $query) => $query->where('channel', $channel));
        } elseif ($channel !== 'All') {
            $builder->doesntHave('globalNotification');
        }
    }

    protected function isLocalized(string $attribute): bool
    {
        return isset($this->data['localized']) || \is_array($this->data[$attribute] ?? null);
    }

    protected function cleanParams(array $params): array
    {
        $cleanParams = [];

        foreach ($params as $key => $param) {
            $cleanParams[$key] = $param ? strip_tags($param) : ucfirst(trans('common.unknown'));
        }

        return $cleanParams;
    }
}

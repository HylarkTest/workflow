<?php

declare(strict_types=1);

namespace App\Core\Account;

use App\Models\Pin;
use App\Models\Base;
use App\Models\Item;
use App\Models\Link;
use App\Models\Note;
use App\Models\Page;
use App\Models\Todo;
use App\Models\Event;
use App\Models\Image;
use App\Models\Mapping;
use App\Models\Category;
use App\Models\Document;
use App\Models\MarkerGroup;
use Markers\Core\MarkerType;
use Illuminate\Support\Carbon;
use Illuminate\Http\UploadedFile;
use App\Core\PlanFeatureRepository;
use Mappings\Core\Mappings\Fields\FieldType;
use AccountIntegrations\Models\IntegrationAccount;

class AccountLimits
{
    public static array $limitedModels = [
        Pin::class,
        Note::class,
        Document::class,
        Image::class,
        Link::class,
        //        Item::class,
        Page::class,
        Todo::class,
        Event::class,
        MarkerGroup::class,
        Category::class,
        IntegrationAccount::class,
    ];

    public readonly PlanFeatureRepository $planFeatureRepository;

    public function __construct(protected Base $base)
    {
        $this->planFeatureRepository = resolve(PlanFeatureRepository::class);
    }

    public function getHistoryLogStartDate(): ?Carbon
    {
        $daysLimit = $this->getLimit('log');

        if ($daysLimit === -1) {
            return null;
        }

        return now()->subDays((int) $daysLimit);
    }

    public function getExistingAmount(string $feature): int
    {
        $base = $this->base;

        return match ($feature) {
            'storage' => $base->documents()->sum('size') + $base->images()->sum('size'),
            'records' => $base->items->count(),
            'tag_groups' => $base->markerGroups()->where('type', MarkerType::TAG->value)->count(),
            'pipeline_groups' => $base->markerGroups()->where('type', MarkerType::PIPELINE->value)->count(),
            'status_groups' => $base->markerGroups()->where('type', MarkerType::STATUS->value)->count(),
            'pinboard' => $base->pins()->count(),
            'integrations' => $base->integrationAccounts->count(),
            default => $base->{$feature}()->count(),
        };
    }

    public function canUploadFile(UploadedFile $file): bool
    {
        return ! $this->exceedsLimit('storage', $file->getSize());
    }

    public function canCreateSpaces(): bool
    {
        return $this->hasRemaining('spaces');
    }

    public function canCreatePages(int $count = 1): bool
    {
        return ! $this->exceedsLimit('pages', $count);
    }

    public function canCreateEntities(): bool
    {
        return $this->hasRemaining('records');
    }

    public function canCreateMarkers(MarkerType $type, int $count = 1): bool
    {
        $limitKey = match ($type) {
            MarkerType::TAG => 'tag_groups',
            MarkerType::PIPELINE => 'pipeline_groups',
            MarkerType::STATUS => 'status_groups',
        };

        return ! $this->exceedsLimit($limitKey, $count);
    }

    public function canCreateCategories(int $count = 1): bool
    {
        return ! $this->exceedsLimit('categories', $count);
    }

    public function canCreateTodos(): bool
    {
        return $this->hasRemaining('todos');
    }

    public function canCreateEvents(): bool
    {
        return $this->hasRemaining('events');
    }

    public function canCreateNotes(): bool
    {
        return $this->hasRemaining('notes');
    }

    public function canCreatePins(): bool
    {
        return $this->hasRemaining('pinboard');
    }

    public function canCreateLinks(): bool
    {
        return $this->hasRemaining('links');
    }

    public function canAddIntegrations(): bool
    {
        return $this->hasRemaining('integrations');
    }

    public function canAssociateEmailAddresses(Item $item): bool
    {
        $limit = $this->getLimit('email_associations');
        $existingCount = $item->emailAddressables()->count();

        return $existingCount < $limit;
    }

    public function hasRemaining(string $feature): bool
    {
        return $this->remainingCount($feature) > 0;
    }

    public function exceedsLimit(string $feature, int $afterAdding = 0): bool
    {
        return $this->remainingCount($feature, $afterAdding) < 0;
    }

    public function remainingCount(string $feature, int $afterAdding = 0): int
    {
        $limit = $this->getLimit($feature);
        $existingCount = $this->getExistingAmount($feature);

        return $limit - ($existingCount + $afterAdding);
    }

    public function getFieldLimit(): int
    {
        return 50;
    }

    public function canAddAField(Mapping $mapping, array $field): bool
    {
        $limit = $this->getFieldLimit();

        $count = $mapping->fields->fullCount();
        $count = $this->getFieldCount([$field], $count);

        return $count <= $limit;
    }

    public function canCreateMappingWithFields(array $fields): bool
    {
        $limit = $this->getFieldLimit();

        $count = $this->getFieldCount($fields);

        return $count <= $limit;
    }

    public function getLimit(string $feature): int
    {
        $limit = $this->planFeatureRepository->getFeatureLimit($feature, $this->base->getCurrentPlan());
        if ($limit === \INF) {
            return -1;
        }
        if ($feature === 'storage') {
            $limit = (int) $limit;
            $limit *= (10 ** 9);
        }

        return (int) $limit;
    }

    protected function getFieldCount(array $fields, int $count = 0): int
    {
        foreach ($fields as $field) {
            $type = $field['type'];
            if ($type === 'MULTI' || ($type instanceof FieldType && $type->is(FieldType::MULTI()))) {
                $count = $this->getFieldCount($field['options']['fields'], $count);
            } else {
                $count++;
            }
        }

        return $count;
    }
}

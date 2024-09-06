<?php

declare(strict_types=1);

namespace App\Providers;

use Mappings\Models\Item;
use Actions\Core\ActionType;
use App\Core\TodoActionType;
use App\Core\ActionTranslator;
use App\Core\MarkerActionType;
use App\Core\MemberActionType;
use App\Core\MappingActionType;
use Markers\Events\MarkerAdded;
use App\Core\ItemActionRecorder;
use Illuminate\Events\Dispatcher;
use Markers\Events\MarkerRemoved;
use App\Core\MappingActionRecorder;
use App\Core\RelationshipActionType;
use Mappings\Events\RelationshipSet;
use Mappings\Events\RelationshipUnset;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\ServiceProvider;
use Mappings\Events\RelationshipsAdded;
use Mappings\Events\RelationshipsRemoved;
use Actions\Core\Contracts\ActionRecorder;
use Mappings\Core\Mappings\Relationships\Relationship;
use App\Core\Actions\ActionTypes\SavedFilterActionType;
use Actions\Core\Contracts\ActionTranslator as ActionTranslatorInterface;

class ActionsServiceProvider extends ServiceProvider
{
    public array $singletons = [
        MappingActionRecorder::class => MappingActionRecorder::class,
        ItemActionRecorder::class => ItemActionRecorder::class,
    ];

    protected ItemActionRecorder $itemRecorder;

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind(ActionTranslatorInterface::class, ActionTranslator::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(Dispatcher $events): void
    {
        // Boot the model so we can then override the recorder
        $userResolver = function () {
            /** @var \App\Models\Base|null $base */
            $base = tenant();

            return $base?->pivot;
        };

        resolve(MappingActionRecorder::class)->setUserResolver($userResolver);
        resolve(ActionRecorder::class)->setUserResolver($userResolver);
        $this->itemRecorder = resolve(ItemActionRecorder::class)->setUserResolver($userResolver);

        ActionType::mergeEnum(MappingActionType::class);
        ActionType::mergeEnum(TodoActionType::class);
        ActionType::mergeEnum(SavedFilterActionType::class);
        ActionType::mergeEnum(RelationshipActionType::class);
        ActionType::mergeEnum(MarkerActionType::class);
        ActionType::mergeEnum(MemberActionType::class);

        $events->listen(RelationshipsAdded::class, function (RelationshipsAdded $event) {
            foreach ($event->children as $child) {
                $this->recordRelationshipEvent($event->parent, $child, $event->relationship, RelationshipActionType::RELATIONSHIP_ADDED());
            }
        });
        $events->listen(RelationshipsRemoved::class, function (RelationshipsRemoved $event) {
            foreach ($event->children as $child) {
                $this->recordRelationshipEvent($event->parent, $child, $event->relationship, RelationshipActionType::RELATIONSHIP_REMOVED());
            }
        });
        $events->listen(RelationshipSet::class, function (RelationshipSet $event) {
            $this->recordRelationshipEvent($event->parent, $event->child, $event->relationship, RelationshipActionType::RELATIONSHIP_ADDED());
        });
        $events->listen(RelationshipUnset::class, function (RelationshipUnset $event) {
            $this->recordRelationshipEvent($event->parent, $event->child, $event->relationship, RelationshipActionType::RELATIONSHIP_REMOVED());
        });

        $events->listen(MarkerAdded::class, function (MarkerAdded $event) {
            resolve(ActionRecorder::class)->recordWithPayload($event->markable, MarkerActionType::MARKER_ADDED(), [
                'marker' => [
                    'name' => $event->marker->name,
                    'group' => $event->marker->group->name,
                    'type' => $event->marker->group->type,
                    'id' => $event->marker->id,
                ],
            ]);
        });

        $events->listen(MarkerRemoved::class, function (MarkerRemoved $event) {
            resolve(ActionRecorder::class)->recordWithPayload($event->markable, MarkerActionType::MARKER_REMOVED(), [
                'marker' => [
                    'name' => $event->marker->name,
                    'group' => $event->marker->group->name,
                    'type' => $event->marker->group->type,
                    'id' => $event->marker->id,
                ],
            ]);
        });
    }

    protected function recordRelationshipEvent(Item $parent, Item $child, Relationship $relationship, ActionType $actionType, bool $checkInverse = true): void
    {
        $this->itemRecorder->recordWithPayload($parent, $actionType, [
            'relationship' => [
                'name' => $relationship->name,
                'id' => $relationship->id(),
            ],
            'related' => [
                'name' => $child->getAttribute('name'),
                'id' => $child->id,
            ],
        ]);
        if ($checkInverse && $inverse = $relationship->inverseRelationship()) {
            $this->recordRelationshipEvent($child, $parent, $inverse, $actionType, false);
        }
    }
}

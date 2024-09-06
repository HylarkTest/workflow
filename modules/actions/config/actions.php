<?php

declare(strict_types=1);

return [
    /*
     | Add listeners to action models to automatically record any changes.
     | If you want to manually record every action you should set this
     | field to false.
     */
    'automatic' => true,

    /*
     | Only record actions if there is an authenticated user that implements the
     | ActionPerformer interface.
     */
    'mandatory_performer' => true,

    /*
     | You can use the HasActions trait to automatically watch a model or you
     | can pass an array of model classes you want to watch.
     */
    'watch' => [],

    /*
     | If you want to extend the base Action model class you should place
     | let the package know with this field.
     */
    'model' => \Actions\Models\Action::class,

    /*
     | The enum class to use for defining different actions.
     */
    'type' => \Actions\Core\ActionType::class,

    /*
     | When a model is force deleted should the actions be deleted as well.
     */
    'cascade' => false,

    /*
     | Specify the columns to be ignored when building the payload.
     */
    'ignore' => ['id', 'created_at', 'createdAt', 'updated_at', 'updatedAt', 'deleted_at', 'deletedAt', 'owner_id', 'owner_type'],

    /*
     | Specify the columns to be ignored when retrieving the payload
     */
    'silent' => [],

    /*
     | Save the name of the performer/subject on the action.
     |
     | "NEVER": The performer/subject name will be fetched from the
     | relationship or null if the model doesn't exist or was deleted.
     |
     | "ALWAYS": The name will be saved on the action when it is created
     | so if the name changes it won't be reflected on previous actions.
     |
     | "ON_DELETE": Set the name of the performer on all their actions when
     | they are deleted (this option will only work for models that use the
     | provided traits).
     |
     | "ON_SOFT_DELETE": Similar to "ON_DELETE" but the name will be set when
     | the model is soft/force deleted and cleared when the model is restored.
     |
     | "ON_UPDATE": Whenever the name of the model changes, the name on all the
     | actions are updated (this option will only work for models that use the
     | traits and have the display key name property set).
     |
     | Supported: "ON_DELETE", "ON_SOFT_DELETE", "ON_UPDATE", "ALWAYS", "NEVER"
     */
    'save_performer_name' => \Actions\Core\NamePersistenceConfig::ON_DELETE,
    'save_subject_name' => \Actions\Core\NamePersistenceConfig::ALWAYS,
];

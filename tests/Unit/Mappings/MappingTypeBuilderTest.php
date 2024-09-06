<?php

declare(strict_types=1);

use App\Models\Mapping;
use Markers\Core\MarkerType;
use Markers\Models\MarkerGroup;
use GraphQL\Utils\SchemaPrinter;
use App\GraphQL\AST\BuildDynamicApi;
use Nuwave\Lighthouse\Schema\SchemaBuilder;
use Mappings\Core\Mappings\Fields\FieldType;
use App\Core\Mappings\Features\MappingFeatureType;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mappings\Core\Mappings\Relationships\RelationshipType;

uses(RefreshDatabase::class);

test('a mapping can be converted to a graphql schema', function () {
    $user = createUser();

    /** @var \App\Models\MarkerGroup $markerGroup */
    $markerGroup = MarkerGroup::factory()->withMarkers()->create([
        'name' => 'Client markers',
    ]);

    $companyMapping = Mapping::query()->forceCreate([
        'name' => 'Companies',
        'fields' => [[
            'name' => 'Name',
            'type' => FieldType::NAME(),
            'options' => ['rules' => ['required' => true]],
        ]],
    ]);

    $projectMapping = Mapping::query()->forceCreate([
        'name' => 'Projects',
        'fields' => [[
            'name' => 'Name',
            'type' => FieldType::NAME(),
            'options' => ['rules' => ['required' => true]],
        ]],
    ]);

    /** @var \App\Models\Mapping $clientMapping */
    $clientMapping = Mapping::query()->forceCreate([
        'name' => 'Clients',
        'fields' => [
            [
                'id' => 'nameId',
                'name' => 'Name',
                'type' => FieldType::NAME(),
                'options' => ['rules' => ['required' => true]],
            ],
            [
                'id' => 'emailId',
                'name' => 'Emails',
                'type' => FieldType::EMAIL(),
                'options' => ['list' => true],
            ],
            [
                'id' => 'phoneId',
                'name' => 'Phones',
                'type' => FieldType::PHONE(),
                'options' => ['list' => true, 'labeled' => ['labels' => ['Work', 'Home', 'Other']]],
            ],
            [
                'id' => 'documentId',
                'name' => 'Documents',
                'type' => FieldType::FILE(),
                'options' => ['labeled' => ['freeText' => true]],
            ],
            [
                'id' => 'multiId',
                'name' => 'Multi',
                'type' => FieldType::MULTI(),
                'options' => ['list' => true, 'labeled' => ['freeText' => true], 'fields' => [
                    ['id' => 'multiLineId', 'name' => 'Multi line', 'type' => FieldType::LINE()],
                ]],
            ],
        ],
        'relationships' => [
            [
                'id' => 'companyRelationshipId',
                'name' => 'Company',
                'to' => $companyMapping,
                'type' => RelationshipType::ONE_TO_ONE,
            ],
            [
                'id' => 'projectsRelationshipId',
                'name' => 'Projects',
                'to' => $projectMapping,
                'type' => RelationshipType::ONE_TO_MANY,
            ],
        ],
    ]);

    $clientMapping->addMarkerGroup([
        'id' => 'tagMarkerId',
        'type' => MarkerType::TAG,
        'name' => 'Client markers',
        'group' => $markerGroup,
    ]);

    $clientMapping->addMarkerGroup([
        'id' => 'statusMarkerId',
        'type' => MarkerType::STATUS,
        'name' => 'Client status',
        'group' => $markerGroup,
    ]);

    $clientMapping->addMarkerGroup([
        'id' => 'pipelineMarkerId',
        'type' => MarkerType::PIPELINE,
        'name' => 'Client project pipeline',
        'group' => $markerGroup,
        'relationship' => $clientMapping->relationships->find('projectsRelationshipId'),
    ]);

    $clientMapping->enableFeature(MappingFeatureType::TODOS);
    $clientMapping->enableFeature(MappingFeatureType::EVENTS);
    $clientMapping->enableFeature(MappingFeatureType::NOTES);
    $clientMapping->enableFeature(MappingFeatureType::DOCUMENTS);
    $clientMapping->enableFeature(MappingFeatureType::PINBOARD);
    $clientMapping->enableFeature(MappingFeatureType::LINKS);
    $clientMapping->enableFeature(MappingFeatureType::PRIORITIES);
    $clientMapping->enableFeature(MappingFeatureType::FAVORITES);
    $clientMapping->enableFeature(MappingFeatureType::EMAILS);

    $schema = resolve(SchemaBuilder::class)->schema();
    resolve(BuildDynamicApi::class)->build($user->firstPersonalBase());

    $schemaString = SchemaPrinter::doPrint($schema);

    $types = [
        /* @lang GraphQL */ <<<'SDL'
        type ItemQuery {
          company(id: ID!): CompanyItem!
          companies(forRelation: RelationQueryInput, filter: [ItemFilterInput!], orderBy: [OrderByClause!], first: Int! = 25, after: String): CompanyItemConnection!
          project(id: ID!): ProjectItem!
          projects(forRelation: RelationQueryInput, filter: [ItemFilterInput!], orderBy: [OrderByClause!], first: Int! = 25, after: String): ProjectItemConnection!
          client(id: ID!): ClientItem!
          clients(forRelation: RelationQueryInput, filter: [ItemFilterInput!], orderBy: [OrderByClause!], first: Int! = 25, after: String): ClientItemConnection!
        }
        SDL,
        /* @lang GraphQL */ <<<'SDL'
        type GroupedItemQuery {
          companies(group: String!, includeGroups: [String], excludeGroups: [String], forRelation: RelationQueryInput, filter: [ItemFilterInput!], orderBy: [OrderByClause!], first: Int! = 25, after: String): CompanyItemGrouped!
          projects(group: String!, includeGroups: [String], excludeGroups: [String], forRelation: RelationQueryInput, filter: [ItemFilterInput!], orderBy: [OrderByClause!], first: Int! = 25, after: String): ProjectItemGrouped!
          clients(group: String!, includeGroups: [String], excludeGroups: [String], forRelation: RelationQueryInput, filter: [ItemFilterInput!], orderBy: [OrderByClause!], first: Int! = 25, after: String): ClientItemGrouped!
        }
        SDL,
        /* @lang GraphQL */ <<<'SDL'
        type ItemMutation {
          companies: CompanyItemMutation!
          projects: ProjectItemMutation!
          clients: ClientItemMutation!
        }
        SDL,
        /* @lang GraphQL */ <<<'SDL'
        type ItemSubscription {
          companies: CompanyItemSubscription!
          projects: ProjectItemSubscription!
          clients: ClientItemSubscription!
        }
        SDL,
        /* @lang GraphQL */ <<<'SDL'
        type ClientCompanyRelationEdge {
          node: CompanyItem!
        }
        SDL,
        /* @lang GraphQL */ <<<'SDL'
        type ClientItem implements Item & Findable & Markable & Assignable {
          id: ID!
          spaceId: ID!
          name: String!
          image: ItemImage
          names: [StringFieldValue!]!
          images: [ItemImageFieldValue!]!
          emails: [Email!]
          data: ClientItemData
          mapping: Mapping!
          pages: [ItemPage!]!
          markerGroups: [MarkerCollection!]
          assigneeGroups: [AssigneeInfo!]!
          deadlines: DeadlineInfo!
          isFavorite: Boolean!
          priority: Int!
          createdAt: DateTime!
          updatedAt: DateTime!
          relations: ClientRelations!
          features: ClientItemFeatures!
          markers: ClientItemMarkers!
        }
        SDL,
        /* @lang GraphQL */ <<<'SDL'
        type ClientItemConnection {
          edges: [ClientItemEdge!]!
          pageInfo: PageInfo!
        }
        SDL,
        /* @lang GraphQL */ <<<'SDL'
        type ClientItemGrouped {
          groups: [ClientItemGroupedConnection!]!
        }
        SDL,
        /* @lang GraphQL */ <<<'SDL'
        type ClientItemGroupedConnection {
          groupHeader: String
          group: Groupable
          edges: [ClientItemEdge!]!
          pageInfo: PageInfo!
        }
        SDL,
        /* @lang GraphQL */ <<<'SDL'
        input ClientItemCreateInput {
          data: ClientItemDataInput
          markers: [MarkersInput!]
        }
        SDL,
        /* @lang GraphQL */ <<<'SDL'
        type ClientMultiMulti {
          multiLine(truncate: Int, suffix: String = "..."): StringValue
        }
        SDL,
        /* @lang GraphQL */ <<<'SDL'
        input ClientMultiMultiInput {
          multiLine: StringValueInput
        }
        SDL,
        /* @lang GraphQL */ <<<'SDL'
        type ClientMultiMultiValue {
          label: String
          labelKey: String
          fieldValue: ClientMultiMulti
          main: Boolean
        }
        SDL,
        /* @lang GraphQL */ <<<'SDL'
        input ClientMultiMultiValueInput {
          label: String
          labelKey: String
          fieldValue: ClientMultiMultiInput
          main: Boolean
        }
        SDL,
        /* @lang GraphQL */ <<<'SDL'
        input ClientMultiMultiListValueInput {
          listValue: [ClientMultiMultiValueInput!]
        }
        SDL,
        /* @lang GraphQL */ <<<'SDL'
        type ClientItemData {
          name: StringValue
          emails: StringListValue
          phones: StringListValue
          documents: FileValue
          multi: ClientMultiMultiListValue
        }
        SDL,
        /* @lang GraphQL */ <<<'SDL'
        input ClientItemDataInput {
          name: StringValueInput
          emails: StringListValueInput
          phones: StringListValueInput
          documents: UploadValueInput
          multi: ClientMultiMultiListValueInput
        }
        SDL,
        /* @lang GraphQL */ <<<'SDL'
        input ClientItemDuplicateInput {
          id: ID!
          withMarkers: Boolean! = false
          withRelationships: Boolean! = false
          withAssignee: Boolean! = false
          withFeaturesTodos: Boolean! = false
          withFeaturesEvents: Boolean! = false
          withFeaturesDocuments: Boolean! = false
          withFeaturesLinks: Boolean! = false
          withFeaturesPins: Boolean! = false
          withFeaturesNotes: Boolean! = false
          withFeaturesTimekeeper: Boolean! = false
        }
        SDL,
        /* @lang GraphQL */ <<<'SDL'
        input ClientItemDeleteInput {
          id: ID!
          force: Boolean! = false
        }
        SDL,
        /* @lang GraphQL */ <<<'SDL'
        type ClientItemEdge {
          node: ClientItem!
          cursor: String!
        }
        SDL,
        /* @lang GraphQL */ <<<'SDL'
        type ClientItemFeatures {
          todos(filter: TodoFilter! = ALL, orderBy: [TodoOrderBy!], first: Int! = 25, after: String): TodoConnection!
          externalTodos(dueBefore: DateTime, dueAfter: DateTime, filter: TodoFilter! = ALL, first: Int! = 25, page: Int): ExternalTodoPaginator!
          events(orderBy: [EventOrderBy!], includeRecurringInstances: Boolean! = false, startsBefore: DateTime, startsAfter: DateTime, endsBefore: DateTime, endsAfter: DateTime, first: Int! = 25, after: String): EventConnection!
          externalEvents(startsBefore: DateTime, endsAfter: DateTime, first: Int! = 25, page: Int): ExternalEventPaginator!
          notes(filter: NoteFilter! = ALL, orderBy: [NoteOrderBy!], first: Int! = 25, after: String): NoteConnection!
          pins(filter: PinFilter! = ALL, orderBy: [PinOrderBy!], first: Int! = 25, after: String): PinConnection!
          documents(filter: DocumentFilter! = ALL, orderBy: [DocumentOrderBy!], first: Int! = 25, after: String): DocumentConnection!
          links(filter: LinkFilter! = ALL, orderBy: [LinkOrderBy!], first: Int! = 25, after: String): LinkConnection!
          emails(first: Int! = 25, page: String): EmailConnection!
          emailAssociations: EmailAssociations!
        }
        SDL,
        /* @lang GraphQL */ <<<'SDL'
        type ClientItemMarkers {
          clientMarkers: [Marker!]
          clientStatus: Marker
        }
        SDL,
        /* @lang GraphQL */ <<<'SDL'
        type ClientItemMutation {
          createClient(input: ClientItemCreateInput!): ClientItemMutationResponse!
          updateClient(input: ClientItemUpdateInput!): ClientItemMutationResponse!
          deleteClient(input: ClientItemDeleteInput!): ClientItemMutationResponse!
          duplicateClient(input: ClientItemDuplicateInput!): ClientItemMutationResponse!
          setCompanyRelationship(input: AddSingleRelationshipInput!): ClientItemMutationResponse!
          removeCompanyRelationship(input: RemoveSingleRelationshipInput!): ClientItemMutationResponse!
          addToProjectsRelationship(input: AddManyRelationshipsInput!): ClientItemMutationResponse!
          removeFromProjectsRelationship(input: RemoveManyRelationshipsInput!): ClientItemMutationResponse!
        }
        SDL,
        /* @lang GraphQL */ <<<'SDL'
        type ClientItemMutationResponse implements MutationResponse {
          code: String
          success: Boolean
          message: String
          client: ClientItem
        }
        SDL,
        /* @lang GraphQL */ <<<'SDL'
        input ClientItemUpdateInput {
          id: ID!
          data: ClientItemDataInput
          isFavorite: Boolean
          priority: Int
        }
        SDL,
        /* @lang GraphQL */ <<<'SDL'
        type ClientProjectsRelationConnection {
          edges: [ClientProjectsRelationEdge!]!
          pageInfo: PageInfo!
        }
        SDL,
        /* @lang GraphQL */ <<<'SDL'
        type ClientProjectsRelationEdge {
          node: ProjectItem!
          cursor: String!
          markers: ClientProjectsRelationMarkers
        }
        SDL,
        /* @lang GraphQL */ <<<'SDL'
        type ClientProjectsRelationMarkers {
          clientProjectPipeline: [Marker!]
        }
        SDL,
        /* @lang GraphQL */ <<<'SDL'
        type ClientRelations {
          company: ClientCompanyRelationEdge
          projects(first: Int! = 25, after: String): ClientProjectsRelationConnection!
        }
        SDL,
        /* @lang GraphQL */ <<<'SDL'
        type ClientItemSubscription {
          clientCreated: ClientItemMutationResponse
          clientUpdated: ClientItemMutationResponse
          clientDeleted: ClientItemMutationResponse
        }
        SDL,
    ];

    foreach ($types as $type) {
        expect($type)->toContain($type);
    }
});

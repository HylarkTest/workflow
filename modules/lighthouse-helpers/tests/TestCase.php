<?php

declare(strict_types=1);

namespace Tests\LighthouseHelpers;

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Testing\WithFaker;
use LighthouseHelpers\GraphQLServiceProvider;
use Orchestra\Testbench\TestCase as BaseTestCase;
use Nuwave\Lighthouse\Schema\Source\SchemaSourceProvider;

class TestCase extends BaseTestCase
{
    use InteractsWithGraphQLExceptionHandling;
    use WithFaker;

    public static string $schema;

    protected function defineEnvironment($app): void
    {
        $app['config']->set('lighthouse.debug', 15);
        $app['config']->set('app.debug', true);
        $app['config']->set('lighthouse.enable', false);
    }

    protected function setSchema(string $schema): void
    {
        static::$schema = $schema;
    }

    protected function getPackageProviders($app): array
    {
        return [GraphQLServiceProvider::class];
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->setUpFaker();

        $this->withoutExceptionHandling();
        //        $this->withoutGraphQLExceptionHandling();

        config([
            'database.default' => 'sqlite',
            'database.connections.sqlite.database' => ':memory:',
            'lighthouse.schema_cache.enable' => false,
        ]);

        Schema::create('test_items', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('data')->nullable();
            $table->string('color')->nullable();
            $table->timestamps();
        });
        Schema::create('test_children', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->unsignedBigInteger('item_id');
            $table->timestamps();
        });

        $this->app->singleton(SchemaSourceProvider::class, function (): SchemaSourceProvider {
            return new class implements SchemaSourceProvider
            {
                public function setRootPath(string $path): void {}

                public function getSchemaString(): string
                {
                    return TestCase::$schema;
                }
            };
        });
    }
}

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use LighthouseHelpers\Concerns\HasGlobalId;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TestItem extends Model
{
    use HasGlobalId;

    protected $guarded = [];

    protected $table = 'test_items';

    protected $casts = ['data' => 'json'];

    public function children(): HasMany
    {
        return $this->hasMany(TestChild::class, 'item_id');
    }
}

class TestChild extends Model
{
    use HasGlobalId;

    protected $table = 'test_children';

    protected $guarded = [];
}

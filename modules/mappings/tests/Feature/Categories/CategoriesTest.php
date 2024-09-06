<?php

declare(strict_types=1);

namespace Tests\Mappings\Feature\Categories;

use Tests\Mappings\TestCase;
use Mappings\Models\Category;

class CategoriesTest extends TestCase
{
    public static function resolveCreateCategory($_, array $args, AppContext $context): Category
    {
        /** @var \Mappings\Models\Category $category */
        $category = Category::query()->forceCreate(['name' => $args['name']]);
        $category->items()->createMany(collect($args['items'])->map(fn (string $name) => ['name' => $name]));

        return $category;
    }
}

class User extends \Illuminate\Foundation\Auth\User {}

<?php

declare(strict_types=1);

namespace Tests\LighthouseHelpers;

use BenSampo\Enum\Enum;
use PHPUnit\Framework\TestCase;
use Illuminate\Database\Eloquent\Model;
use LighthouseHelpers\Concerns\ConvertsCamelCaseAttributes;

class CamelCaseModelTest extends TestCase
{
    /**
     * A model can store camel case attributes
     *
     * @test
     */
    public function a_model_can_store_camel_case_attributes(): void
    {
        $item = new Item;

        $item->camelCase = 'value';

        static::assertSame('value', $item->getAttributes()['camel_case']);
    }

    /**
     * A model can retrieve camel case attributes
     *
     * @test
     */
    public function a_model_can_retrieve_camel_case_attributes(): void
    {
        $item = new Item;

        $item->camel_case = 'value';

        static::assertSame('value', $item->camelCase);
    }

    /**
     * A model can be filled with camel case attributes
     *
     * @test
     */
    public function a_model_can_be_filled_with_camel_case_attributes(): void
    {
        $item = new Item;

        $item->fill(['camelCase' => 'value']);

        static::assertSame('value', $item->getAttributes()['camel_case']);
    }

    /**
     * A model with the enum trait can have camel case attributes
     *
     * @test
     */
    public function a_model_with_the_enum_trait_can_have_camel_case_attributes(): void
    {
        $item = new ItemWithEnum;

        $item->enumKey = ItemEnum::KEY();

        static::assertTrue($item->getAttributes()['enum_key'] === 'KEY');
    }

    /**
     * A model with the enum trait can retrieve camel case attributes
     *
     * @test
     */
    public function a_model_with_the_enum_trait_can_retrieve_camel_case_attributes(): void
    {
        $item = new ItemWithEnum;

        $item->enum_key = ItemEnum::KEY();

        static::assertTrue($item->enumKey->is(ItemEnum::KEY()));
    }
}

class Item extends Model
{
    use ConvertsCamelCaseAttributes;

    protected $fillable = ['camel_case'];
}

class ItemWithEnum extends Model
{
    use ConvertsCamelCaseAttributes;

    protected $casts = ['enum_key' => ItemEnum::class];
}

class ItemEnum extends Enum
{
    private const KEY = 'KEY';
}

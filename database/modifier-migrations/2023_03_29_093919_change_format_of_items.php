<?php

declare(strict_types=1);

use Illuminate\Support\Facades\DB;
use Mappings\Core\Mappings\Fields\Field;
use LaravelUtils\Database\KnowsConnection;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    use KnowsConnection;

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::table('items')
            ->eachById(function (stdClass $item) {
                $data = json_decode($item->data, true, 512, \JSON_THROW_ON_ERROR);
                $data = $this->formatData($data);
                DB::table('items')
                    ->where('id', $item->id)
                    ->update(['data' => json_encode($data, \JSON_THROW_ON_ERROR)]);
            });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {}

    protected function formatData($value)
    {
        if (! is_array($value)) {
            return $value;
        }
        if (array_key_exists('labeledValue', $value)) {
            $value[Field::VALUE] = $value['labeledValue'];
            unset($value['labeledValue']);
        }
        if (array_key_exists('label', $value)) {
            $value[Field::LABEL] = $value['label'];
            unset($value['label']);
        }

        return collect($value)->mapWithKeys(function ($value, $key) {
            return [$key => $this->formatData($value)];
        })->toArray();
    }
};

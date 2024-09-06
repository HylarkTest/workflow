<?php

declare(strict_types=1);

use App\Models\Pin;
use App\Models\Link;
use App\Models\Note;
use App\Models\Todo;
use App\Models\Event;
use App\Models\Document;
use Illuminate\Support\Str;
use CitusLaravel\CitusHelpers;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use LaravelUtils\Database\KnowsConnection;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    use CitusHelpers;
    use KnowsConnection;

    protected $tables = [
        'todoables' => Todo::class,
        'notables' => Note::class,
        'eventables' => Event::class,
        'linkables' => Link::class,
        'pinables' => Pin::class,
        'attachables' => Document::class,
    ];

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::dropIfExists('notables');

        foreach ($this->tables as $t => $model) {
            $this->createTableForDistribution($t, 'base_id', function (Blueprint $table) use ($t, $model) {
                /** @var \Illuminate\Database\Eloquent\Model $model */
                $model = new $model;
                $table->morphs(Str::singular($t));
                if ($t === 'todoables' || $t === 'eventables') {
                    $table->string('external_id')->nullable();
                }
                $table->unsignedBigInteger($model->getForeignKey());
                $table->boolean('is_system_link')->default(false);
                $table->timestamps();
                if ($this->usingSqliteConnection()) {
                    $table->foreign($model->getForeignKey())->references('id')->on($model->getTable())->cascadeOnDelete();
                } else {
                    $table->foreign(['base_id', $model->getForeignKey()])->references(['base_id', 'id'])->on($model->getTable())->cascadeOnDelete();
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        foreach ($this->tables as $t => $model) {
            Schema::dropIfExists($t);
        }
    }
};

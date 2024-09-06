<?php

declare(strict_types=1);

namespace LaravelUtils\Database;

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\MySqlConnection;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\SQLiteConnection;
use Illuminate\Database\PostgresConnection;
use Illuminate\Database\Schema\ColumnDefinition;

trait KnowsConnection
{
    protected function usingSqliteConnection(): bool
    {
        return Schema::connection(null)->getConnection() instanceof SQLiteConnection;
    }

    protected function usingPostgresConnection(): bool
    {
        return Schema::connection(null)->getConnection() instanceof PostgresConnection;
    }

    protected function usingMySqlConnection(): bool
    {
        return Schema::connection(null)->getConnection() instanceof MySqlConnection;
    }

    protected function jsonOrStringColumn(Blueprint $table, string $column, string $textType = 'text'): ColumnDefinition
    {
        if ($this->usingSqliteConnection()) {
            return $table->$textType($column);
        }

        return $table->json($column);
    }

    protected function nullableJsonOrStringColumn(Blueprint $table, string $column, string $textType = 'text'): ColumnDefinition
    {
        return $this->jsonOrStringColumn($table, $column, $textType)->nullable();
    }
}

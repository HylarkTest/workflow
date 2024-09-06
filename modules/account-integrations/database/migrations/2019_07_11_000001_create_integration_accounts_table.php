<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use LaravelUtils\Database\KnowsConnection;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    use KnowsConnection;

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('integration_accounts', function (Blueprint $table) {
            $table->id();
            $table->morphs('account_owner');
            $table->string('provider'); // Microsoft/Apple/Google/etc.
            $table->string('account_name'); // Typically, the email address for the account
            // The things that the user wants to access through the app (calendar/mail/tasks/etc.)
            $this->nullableJsonOrStringColumn($table, 'scopes');
            $table->string('provider_id'); // The identifier used by the provider for the owner
            $table->text('token');
            $table->text('refresh_token');
            $table->string('expires_at');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('integration_accounts');
    }
};

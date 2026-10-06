<?php

use Filament\TeamChat\Support\TeamChatSchema;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tc_user_statuses', function (Blueprint $table) {
            $table->id();
            TeamChatSchema::userForeignId($table)->unique()->constrained(TeamChatSchema::userTable())->cascadeOnDelete();
            $table->string('display_name')->nullable();
            $table->string('avatar_url')->nullable();
            $table->string('status_text')->nullable();
            $table->string('status_emoji')->nullable();
            $table->boolean('is_online')->default(false);
            $table->timestamp('last_seen_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tc_user_statuses');
    }
};

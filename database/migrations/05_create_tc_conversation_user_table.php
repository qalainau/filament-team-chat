<?php

use Filament\TeamChat\Support\TeamChatSchema;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tc_conversation_user', function (Blueprint $table) {
            $table->id();
            $table->foreignId('conversation_id')->constrained('tc_conversations')->cascadeOnDelete();
            TeamChatSchema::userForeignId($table)->constrained(TeamChatSchema::userTable())->cascadeOnDelete();
            $table->boolean('is_muted')->default(false);
            $table->timestamps();

            $table->unique(['conversation_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tc_conversation_user');
    }
};

<?php

use Filament\TeamChat\Support\TeamChatSchema;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tc_reactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('message_id')->constrained('tc_messages')->cascadeOnDelete();
            TeamChatSchema::userForeignId($table)->constrained(TeamChatSchema::userTable())->cascadeOnDelete();
            $table->string('emoji', 32);
            $table->timestamps();

            $table->unique(['message_id', 'user_id', 'emoji']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tc_reactions');
    }
};

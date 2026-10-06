<?php

use Filament\TeamChat\Support\TeamChatSchema;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tc_channel_user', function (Blueprint $table) {
            $table->id();
            $table->foreignId('channel_id')->constrained('tc_channels')->cascadeOnDelete();
            TeamChatSchema::userForeignId($table)->constrained(TeamChatSchema::userTable())->cascadeOnDelete();
            $table->string('role')->default('member');
            $table->boolean('is_muted')->default(false);
            $table->timestamp('joined_at')->useCurrent();
            $table->timestamps();

            $table->unique(['channel_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tc_channel_user');
    }
};

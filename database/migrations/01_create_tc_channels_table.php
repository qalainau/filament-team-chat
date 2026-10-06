<?php

use Filament\TeamChat\Support\TeamChatSchema;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tc_channels', function (Blueprint $table) {
            $table->id();
            TeamChatSchema::teamId($table);
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->string('topic')->nullable();
            $table->string('type')->default('public');
            TeamChatSchema::userForeignId($table, 'created_by')->constrained(TeamChatSchema::userTable())->cascadeOnDelete();
            $table->timestamp('archived_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tc_channels');
    }
};

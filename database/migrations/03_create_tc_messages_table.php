<?php

use Filament\TeamChat\Support\TeamChatSchema;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tc_messages', function (Blueprint $table) {
            $table->id();
            $table->morphs('messageable');
            TeamChatSchema::userForeignId($table)->constrained(TeamChatSchema::userTable())->cascadeOnDelete();
            $table->foreignId('parent_id')->nullable()->constrained('tc_messages')->nullOnDelete();
            $table->text('body');
            $table->text('body_html');
            $table->timestamp('edited_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['messageable_type', 'messageable_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tc_messages');
    }
};

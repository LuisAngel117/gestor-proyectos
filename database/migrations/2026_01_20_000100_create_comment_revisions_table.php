<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('comment_revisions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('comment_id')->constrained('comments')->cascadeOnDelete();
            $table->text('body');
            $table->foreignId('edited_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('edited_at');
            $table->unsignedInteger('lock_version_before');
            $table->timestamps();

            $table->index('comment_id');
            $table->index('edited_by');
            $table->index(['comment_id', 'edited_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('comment_revisions');
    }
};

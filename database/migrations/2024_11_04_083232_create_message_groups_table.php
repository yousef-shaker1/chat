<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('message_groups', function (Blueprint $table) {
            $table->id(); 
            $table->foreignId('sender_id')->constrained('users')->onDelete('cascade'); 
            $table->foreignId('group_id')->constrained('groups')->onDelete('cascade'); 
            $table->text('message')->nullable();
            $table->string('file')->nullable();
            $table->string('type_file')->nullable();
            $table->foreignId('reply_to_message_id')->nullable()->constrained('message_groups')->onDelete('cascade'); 
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('message_groups');
    }
};

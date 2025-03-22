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
        Schema::create('momento_campo_formativo', function (Blueprint $table) {
            $table->id();
            $table->foreignId('momento_id')->constrained()->onDelete('cascade');
            $table->foreignId('campo_formativo_id')->constrained('campo_formativos')->onDelete('cascade');
            $table->timestamps();

            // Prevent duplicate associations
            $table->unique(['momento_id', 'campo_formativo_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('momento_campo_formativo');
    }
};

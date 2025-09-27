<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('dietary_tag_recipe', function (Blueprint $table) {
            $table->foreignId('dietary_tag_id')->constrained()->cascadeOnDelete();
            $table->foreignId('recipe_id')->constrained()->cascadeOnDelete();
            $table->primary(['dietary_tag_id', 'recipe_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dietary_tag_recipe');
    }
};

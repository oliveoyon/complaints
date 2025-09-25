<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSeverityLevelsTable extends Migration
{
    public function up(): void
    {
        Schema::create('severity_levels', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique(); // e.g., Low, Medium, High, Urgent
            $table->text('description')->nullable();
            $table->integer('priority')->default(1); // optional, for sorting/severity logic
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('severity_levels');
    }
}

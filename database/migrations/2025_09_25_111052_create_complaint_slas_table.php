<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateComplaintSlasTable extends Migration
{
    public function up(): void
    {
        Schema::create('complaint_slas', function (Blueprint $table) {
            $table->id();

            $table->foreignId('complaint_id')->constrained()->cascadeOnDelete();

            $table->dateTime('due_at'); // when complaint should be resolved
            $table->boolean('escalated')->default(false); // whether it has been escalated
            $table->foreignId('escalated_to')->nullable()->constrained('users')->nullOnDelete(); // who it was escalated to
            $table->text('remarks')->nullable(); // optional notes

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('complaint_slas');
    }
}

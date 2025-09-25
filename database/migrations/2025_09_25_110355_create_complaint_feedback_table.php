<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateComplaintFeedbackTable extends Migration
{
    public function up(): void
    {
        Schema::create('complaint_feedbacks', function (Blueprint $table) {
            $table->id();

            // reference the complaint
            $table->foreignId('complaint_id')->constrained()->cascadeOnDelete();

            // who is giving feedback (student/guardian)
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();

            // rating: 1-5 or similar
            $table->tinyInteger('rating')->nullable();

            // optional text feedback
            $table->text('feedback')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('complaint_feedbacks');
    }
}

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateComplaintStatusHistoriesTable extends Migration
{
    public function up(): void
    {
        Schema::create('complaint_status_histories', function (Blueprint $table) {
            $table->id();

            // the complaint this status belongs to
            $table->foreignId('complaint_id')->constrained()->cascadeOnDelete();

            // who changed the status
            $table->foreignId('changed_by')->constrained('users')->cascadeOnDelete();

            $table->string('old_status');
            $table->string('new_status');

            $table->text('remarks')->nullable(); // optional comment

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('complaint_status_histories');
    }
}

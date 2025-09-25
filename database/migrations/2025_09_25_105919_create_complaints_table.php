<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateComplaintsTable extends Migration
{
    public function up(): void
    {
        Schema::create('complaints', function (Blueprint $table) {
            $table->id();

            // who raised the complaint (student/guardian)
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();

            // config tables we'll create next (nullable for flexibility)
            $table->foreignId('category_id')->nullable()->constrained('complaint_categories')->nullOnDelete();
            $table->foreignId('severity_id')->nullable()->constrained('severity_levels')->nullOnDelete();

            $table->string('title')->nullable();
            $table->text('description');

            // status controlled by constants in model; indexed for queries
            $table->string('status')->default('received')->index();

            // who is currently responsible (teacher, hod, vp, etc.)
            $table->foreignId('assigned_to')->nullable()->constrained('users')->nullOnDelete();

            // optional department relation
            $table->foreignId('department_id')->nullable()->constrained('departments')->nullOnDelete();

            // hide identity when true
            $table->boolean('is_anonymous')->default(false);

            // simple way to store multiple file refs without an attachments table (can refactor later)
            $table->json('attachments')->nullable();

            $table->timestamps();
            $table->softDeletes(); // optional but recommended for history/audit
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('complaints');
    }
}

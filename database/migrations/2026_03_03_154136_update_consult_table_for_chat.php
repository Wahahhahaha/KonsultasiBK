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
        Schema::table('consult', function (Blueprint $table) {
            $table->enum('status', ['pending', 'active', 'completed', 'cancelled'])->default('pending')->after('problem');
            $table->boolean('student_agree_end')->default(false)->after('status');
            $table->boolean('teacher_agree_end')->default(false)->after('student_agree_end');
            $table->timestamp('updated_at')->nullable()->after('created_at');
        });
    }

    public function down(): void
    {
        Schema::table('consult', function (Blueprint $table) {
            $table->dropColumn(['status', 'student_agree_end', 'teacher_agree_end', 'updated_at']);
        });
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('consult', function (Blueprint $table) {
            $table->text('report_outcome')->nullable()->after('teacher_agree_end');
            $table->boolean('need_follow_up')->default(false)->after('report_outcome');
            $table->text('follow_up_notes')->nullable()->after('need_follow_up');
            $table->integer('follow_up_assigned_teacherid')->nullable()->after('follow_up_notes');
            $table->timestamp('report_submitted_at')->nullable()->after('updated_at');
        });
    }

    public function down(): void
    {
        Schema::table('consult', function (Blueprint $table) {
            $table->dropColumn([
                'report_outcome',
                'need_follow_up',
                'follow_up_notes',
                'follow_up_assigned_teacherid',
                'report_submitted_at'
            ]);
        });
    }
};

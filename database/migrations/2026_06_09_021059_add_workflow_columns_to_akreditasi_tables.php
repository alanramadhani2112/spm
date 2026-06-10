<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('akreditasis', function (Blueprint $table) {
            $table->dateTime('assessment_opened_at')->nullable()->after('status_changed_by');
            $table->dateTime('assessment_submitted_at')->nullable()->after('assessment_opened_at');
        });

        Schema::table('akreditasi_rejections', function (Blueprint $table) {
            $table->foreignId('akreditasi_id')->nullable()->after('id')->constrained('akreditasis')->cascadeOnDelete();
            $table->string('type')->nullable()->after('akreditasi_id');
            $table->string('stage')->nullable()->after('type');
            $table->text('reason')->nullable()->after('stage');
            $table->json('sections')->nullable()->after('reason');
            $table->integer('cycle')->default(0)->after('sections');
            $table->foreignId('rejected_by')->nullable()->after('cycle')->constrained('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('akreditasi_rejections', function (Blueprint $table) {
            $table->dropForeign(['akreditasi_id']);
            $table->dropForeign(['rejected_by']);
            $table->dropColumn(['akreditasi_id', 'type', 'stage', 'reason', 'sections', 'cycle', 'rejected_by']);
        });

        Schema::table('akreditasis', function (Blueprint $table) {
            $table->dropColumn(['assessment_opened_at', 'assessment_submitted_at']);
        });
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('akreditasi_audit_logs', function (Blueprint $table) {
            $table->string('action_type')->nullable()->change();
            $table->string('from_status')->nullable()->after('user_id');
            $table->string('to_status')->nullable()->after('from_status');
            $table->unsignedBigInteger('actor_user_id')->nullable()->after('to_status');
            $table->text('reason')->nullable()->after('actor_user_id');
        });

        Schema::table('akreditasis', function (Blueprint $table) {
            $table->text('status_reason')->nullable()->after('status_changed_by');
        });
    }

    public function down(): void
    {
        Schema::table('akreditasi_audit_logs', function (Blueprint $table) {
            $table->dropColumn(['from_status', 'to_status', 'actor_user_id', 'reason']);
        });

        Schema::table('akreditasis', function (Blueprint $table) {
            $table->dropColumn('status_reason');
        });
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (! Schema::hasColumn('users', 'sso_id')) {
                $table->string('sso_id')->nullable()->unique()->after('uuid');
            }

            if (! Schema::hasColumn('users', 'm_id')) {
                $table->string('m_id')->nullable()->index()->after('sso_id');
            }

            if (! Schema::hasColumn('users', 'nbm')) {
                $table->string('nbm')->nullable()->index()->after('m_id');
            }

            if (! Schema::hasColumn('users', 'phone')) {
                $table->string('phone')->nullable()->after('nbm');
            }

            if (! Schema::hasColumn('users', 'avatar_url')) {
                $table->string('avatar_url')->nullable()->after('phone');
            }

            if (! Schema::hasColumn('users', 'sso_level')) {
                $table->string('sso_level')->nullable()->after('avatar_url');
            }

            if (! Schema::hasColumn('users', 'sso_role')) {
                $table->string('sso_role')->nullable()->after('sso_level');
            }

            if (! Schema::hasColumn('users', 'sso_groups')) {
                $table->json('sso_groups')->nullable()->after('sso_role');
            }

            if (! Schema::hasColumn('users', 'last_sso_login_at')) {
                $table->timestamp('last_sso_login_at')->nullable()->after('sso_groups');
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'sso_id',
                'm_id',
                'nbm',
                'phone',
                'avatar_url',
                'sso_level',
                'sso_role',
                'sso_groups',
                'last_sso_login_at',
            ]);
        });
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('document_categories', function (Blueprint $table) {
            if (! Schema::hasColumn('document_categories', 'code')) {
                $table->string('code')->nullable()->after('name');
            }

            if (! Schema::hasColumn('document_categories', 'visible_to_roles')) {
                $table->json('visible_to_roles')->nullable()->after('required_for_phase');
            }

            if (! Schema::hasColumn('document_categories', 'asesor_scope')) {
                $table->string('asesor_scope')->nullable()->after('visible_to_roles');
            }

            if (! Schema::hasColumn('document_categories', 'template_path')) {
                $table->string('template_path')->nullable()->after('asesor_scope');
            }
        });
    }

    public function down(): void
    {
        Schema::table('document_categories', function (Blueprint $table) {
            $columns = array_values(array_filter([
                Schema::hasColumn('document_categories', 'code') ? 'code' : null,
                Schema::hasColumn('document_categories', 'visible_to_roles') ? 'visible_to_roles' : null,
                Schema::hasColumn('document_categories', 'asesor_scope') ? 'asesor_scope' : null,
                Schema::hasColumn('document_categories', 'template_path') ? 'template_path' : null,
            ]));

            if ($columns !== []) {
                $table->dropColumn($columns);
            }
        });
    }
};

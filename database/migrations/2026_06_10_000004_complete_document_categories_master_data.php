<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('document_categories', function (Blueprint $table) {
            if (! Schema::hasColumn('document_categories', 'name')) {
                $table->string('name')->nullable()->after('id');
            }

            if (! Schema::hasColumn('document_categories', 'description')) {
                $table->text('description')->nullable()->after('name');
            }

            if (! Schema::hasColumn('document_categories', 'required_for_phase')) {
                $table->string('required_for_phase')->nullable()->after('description');
            }

            if (! Schema::hasColumn('document_categories', 'is_active')) {
                $table->boolean('is_active')->default(true)->after('required_for_phase');
            }
        });
    }

    public function down(): void
    {
        Schema::table('document_categories', function (Blueprint $table) {
            $columns = array_values(array_filter([
                Schema::hasColumn('document_categories', 'name') ? 'name' : null,
                Schema::hasColumn('document_categories', 'description') ? 'description' : null,
                Schema::hasColumn('document_categories', 'required_for_phase') ? 'required_for_phase' : null,
                Schema::hasColumn('document_categories', 'is_active') ? 'is_active' : null,
            ]));

            if ($columns !== []) {
                $table->dropColumn($columns);
            }
        });
    }
};

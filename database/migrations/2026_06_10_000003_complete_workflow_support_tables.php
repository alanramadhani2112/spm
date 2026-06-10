<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ipms', function (Blueprint $table) {
            if (! Schema::hasColumn('ipms', 'user_id')) {
                $table->foreignId('user_id')->nullable()->after('id')->constrained()->cascadeOnDelete();
            }

            if (! Schema::hasColumn('ipms', 'data')) {
                $table->json('data')->nullable()->after('user_id');
            }
        });

        Schema::table('sdm_pesantrens', function (Blueprint $table) {
            if (! Schema::hasColumn('sdm_pesantrens', 'user_id')) {
                $table->foreignId('user_id')->nullable()->after('id')->constrained()->cascadeOnDelete();
            }

            if (! Schema::hasColumn('sdm_pesantrens', 'data')) {
                $table->json('data')->nullable()->after('user_id');
            }
        });

        Schema::table('edpms', function (Blueprint $table) {
            if (! Schema::hasColumn('edpms', 'user_id')) {
                $table->foreignId('user_id')->nullable()->after('id')->constrained()->cascadeOnDelete();
            }

            if (! Schema::hasColumn('edpms', 'data')) {
                $table->json('data')->nullable()->after('user_id');
            }
        });

        Schema::table('assessments', function (Blueprint $table) {
            if (! Schema::hasColumn('assessments', 'akreditasi_id')) {
                $table->foreignId('akreditasi_id')->nullable()->after('id')->constrained('akreditasis')->cascadeOnDelete();
            }

            if (! Schema::hasColumn('assessments', 'asesor_id')) {
                $table->foreignId('asesor_id')->nullable()->after('akreditasi_id')->constrained('users')->nullOnDelete();
            }

            if (! Schema::hasColumn('assessments', 'tipe')) {
                $table->string('tipe')->nullable()->after('asesor_id');
            }
        });

        Schema::table('akreditasi_edpms', function (Blueprint $table) {
            if (! Schema::hasColumn('akreditasi_edpms', 'akreditasi_id')) {
                $table->foreignId('akreditasi_id')->nullable()->after('id')->constrained('akreditasis')->cascadeOnDelete();
            }

            if (! Schema::hasColumn('akreditasi_edpms', 'asesor_id')) {
                $table->foreignId('asesor_id')->nullable()->after('akreditasi_id')->constrained('users')->nullOnDelete();
            }

            if (! Schema::hasColumn('akreditasi_edpms', 'butir_id')) {
                $table->foreignId('butir_id')->nullable()->after('asesor_id')->constrained('master_edpm_butirs')->nullOnDelete();
            }

            if (! Schema::hasColumn('akreditasi_edpms', 'value')) {
                $table->decimal('value', 5, 2)->default(0)->after('butir_id');
            }

            if (! Schema::hasColumn('akreditasi_edpms', 'type')) {
                $table->string('type')->nullable()->after('value');
            }
        });

        Schema::table('documents', function (Blueprint $table) {
            if (! Schema::hasColumn('documents', 'akreditasi_id')) {
                $table->foreignId('akreditasi_id')->nullable()->after('id')->constrained('akreditasis')->cascadeOnDelete();
            }

            if (! Schema::hasColumn('documents', 'category_id')) {
                $table->foreignId('category_id')->nullable()->after('akreditasi_id')->constrained('document_categories')->nullOnDelete();
            }

            if (! Schema::hasColumn('documents', 'type')) {
                $table->string('type')->nullable()->after('category_id');
            }

            if (! Schema::hasColumn('documents', 'file_path')) {
                $table->string('file_path')->nullable()->after('type');
            }

            if (! Schema::hasColumn('documents', 'uploaded_by_user_id')) {
                $table->foreignId('uploaded_by_user_id')->nullable()->after('file_path')->constrained('users')->nullOnDelete();
            }
        });

        Schema::table('bandings', function (Blueprint $table) {
            if (! Schema::hasColumn('bandings', 'akreditasi_id')) {
                $table->foreignId('akreditasi_id')->nullable()->after('id')->constrained('akreditasis')->cascadeOnDelete();
            }

            if (! Schema::hasColumn('bandings', 'user_id')) {
                $table->foreignId('user_id')->nullable()->after('akreditasi_id')->constrained()->nullOnDelete();
            }

            if (! Schema::hasColumn('bandings', 'reason')) {
                $table->text('reason')->nullable()->after('user_id');
            }

            if (! Schema::hasColumn('bandings', 'status')) {
                $table->string('status')->default('pending')->after('reason');
            }

            if (! Schema::hasColumn('bandings', 'admin_response')) {
                $table->text('admin_response')->nullable()->after('status');
            }

            if (! Schema::hasColumn('bandings', 'processed_by')) {
                $table->foreignId('processed_by')->nullable()->after('admin_response')->constrained('users')->nullOnDelete();
            }

            if (! Schema::hasColumn('bandings', 'processed_at')) {
                $table->timestamp('processed_at')->nullable()->after('processed_by');
            }
        });
    }

    public function down(): void
    {
        Schema::table('bandings', function (Blueprint $table) {
            $this->dropForeignIfExists($table, 'bandings', 'processed_by');
            $this->dropForeignIfExists($table, 'bandings', 'user_id');
            $this->dropForeignIfExists($table, 'bandings', 'akreditasi_id');
            $table->dropColumn(['akreditasi_id', 'user_id', 'reason', 'status', 'admin_response', 'processed_by', 'processed_at']);
        });

        Schema::table('documents', function (Blueprint $table) {
            $this->dropForeignIfExists($table, 'documents', 'uploaded_by_user_id');
            $this->dropForeignIfExists($table, 'documents', 'category_id');
            $this->dropForeignIfExists($table, 'documents', 'akreditasi_id');
            $table->dropColumn(['akreditasi_id', 'category_id', 'type', 'file_path', 'uploaded_by_user_id']);
        });

        Schema::table('akreditasi_edpms', function (Blueprint $table) {
            $this->dropForeignIfExists($table, 'akreditasi_edpms', 'butir_id');
            $this->dropForeignIfExists($table, 'akreditasi_edpms', 'asesor_id');
            $this->dropForeignIfExists($table, 'akreditasi_edpms', 'akreditasi_id');
            $table->dropColumn(['akreditasi_id', 'asesor_id', 'butir_id', 'value', 'type']);
        });

        Schema::table('assessments', function (Blueprint $table) {
            $this->dropForeignIfExists($table, 'assessments', 'asesor_id');
            $this->dropForeignIfExists($table, 'assessments', 'akreditasi_id');
            $table->dropColumn(['akreditasi_id', 'asesor_id', 'tipe']);
        });

        Schema::table('edpms', function (Blueprint $table) {
            $this->dropForeignIfExists($table, 'edpms', 'user_id');
            $table->dropColumn(['user_id', 'data']);
        });

        Schema::table('sdm_pesantrens', function (Blueprint $table) {
            $this->dropForeignIfExists($table, 'sdm_pesantrens', 'user_id');
            $table->dropColumn(['user_id', 'data']);
        });

        Schema::table('ipms', function (Blueprint $table) {
            $this->dropForeignIfExists($table, 'ipms', 'user_id');
            $table->dropColumn(['user_id', 'data']);
        });
    }

    private function dropForeignIfExists(Blueprint $table, string $tableName, string $column): void
    {
        if (Schema::hasColumn($tableName, $column)) {
            $table->dropForeign([$column]);
        }
    }
};

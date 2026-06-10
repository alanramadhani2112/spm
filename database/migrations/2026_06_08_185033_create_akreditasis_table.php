<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('akreditasis', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('uuid')->unique();
            $table->string('status')->default('draft_profile');

            $table->string('nomor_sk')->nullable();
            $table->text('catatan')->nullable();

            $table->dateTime('tgl_visitasi')->nullable();
            $table->dateTime('tgl_visitasi_akhir')->nullable();

            $table->decimal('na1', 5, 2)->nullable();
            $table->boolean('is_na1_final')->default(false);
            $table->decimal('na2', 5, 2)->nullable();
            $table->boolean('is_na2_final')->default(false);

            $table->decimal('nk', 5, 2)->nullable();
            $table->boolean('is_nk_final')->default(false);

            $table->decimal('nv', 5, 2)->nullable();
            $table->boolean('nv_override')->default(false);
            $table->text('nv_override_reason')->nullable();
            $table->boolean('is_nv_final')->default(false);

            $table->decimal('nilai', 5, 2)->nullable();
            $table->string('peringkat', 1)->nullable();

            $table->string('sertifikat_path')->nullable();
            $table->string('kartu_kendali')->nullable();

            $table->string('laporan_visitasi_asesor1')->nullable();
            $table->string('laporan_visitasi_asesor2')->nullable();
            $table->string('laporan_visitasi_kelompok')->nullable();

            $table->date('masa_berlaku')->nullable();
            $table->date('masa_berlaku_akhir')->nullable();

            $table->dateTime('visitasi_confirmed_at')->nullable();
            $table->text('catatan_visitasi')->nullable();

            $table->text('catatan_rekomendasi_admin')->nullable();

            $table->dateTime('status_changed_at')->nullable();
            $table->integer('status_changed_by')->nullable();
            $table->integer('correction_cycle')->default(0);

            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('akreditasis');
    }
};

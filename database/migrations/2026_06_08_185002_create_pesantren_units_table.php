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
        Schema::create('pesantren_units', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pesantren_id')->constrained()->cascadeOnDelete();
            $table->string('layanan_satuan_pendidikan');
            $table->integer('jumlah_rombel')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pesantren_units');
    }
};

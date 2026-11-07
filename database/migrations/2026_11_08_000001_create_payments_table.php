<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('shops', function (Blueprint $table) {
            $table->string('nama_bank', 50)->nullable()->after('status');
            $table->string('nomor_rekening', 30)->nullable()->after('nama_bank');
            $table->string('atas_nama', 100)->nullable()->after('nomor_rekening');
        });

        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->string('nama_pengirim', 100);
            $table->unsignedBigInteger('jumlah');
            $table->string('bukti');
            $table->string('status', 30)->default('menunggu_verifikasi')->index();
            $table->string('alasan_penolakan', 300)->nullable();
            $table->timestamp('diverifikasi_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');

        Schema::table('shops', function (Blueprint $table) {
            $table->dropColumn(['nama_bank', 'nomor_rekening', 'atas_nama']);
        });
    }
};

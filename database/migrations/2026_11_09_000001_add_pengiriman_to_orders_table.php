<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->string('kurir', 50)->nullable()->after('catatan');
            $table->string('nomor_resi', 60)->nullable()->after('kurir');
            $table->timestamp('diproses_at')->nullable()->after('nomor_resi');
            $table->timestamp('dikirim_at')->nullable()->after('diproses_at');
            $table->timestamp('selesai_at')->nullable()->after('dikirim_at');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['kurir', 'nomor_resi', 'diproses_at', 'dikirim_at', 'selesai_at']);
        });
    }
};

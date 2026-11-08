<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('shops', function (Blueprint $table) {
            $table->boolean('libur')->default(false)->after('atas_nama');
            $table->boolean('jadwal_otomatis')->default(false)->after('libur');
            $table->string('jam_buka', 5)->nullable()->after('jadwal_otomatis');
            $table->string('jam_tutup', 5)->nullable()->after('jam_buka');
            $table->string('hari_buka', 20)->nullable()->after('jam_tutup');
        });

        Schema::table('products', function (Blueprint $table) {
            $table->unsignedInteger('stok_harian')->nullable()->after('stok');
            $table->boolean('pre_order')->default(false)->after('stok_harian');
            $table->unsignedTinyInteger('pre_order_hari')->nullable()->after('pre_order');
        });

        Schema::table('order_items', function (Blueprint $table) {
            $table->unsignedTinyInteger('pre_order_hari')->nullable()->after('jumlah');
        });
    }

    public function down(): void
    {
        Schema::table('order_items', function (Blueprint $table) {
            $table->dropColumn('pre_order_hari');
        });

        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn(['stok_harian', 'pre_order', 'pre_order_hari']);
        });

        Schema::table('shops', function (Blueprint $table) {
            $table->dropColumn(['libur', 'jadwal_otomatis', 'jam_buka', 'jam_tutup', 'hari_buka']);
        });
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('shops', function (Blueprint $table) {
            $table->string('alasan_penolakan', 300)->nullable()->after('status');
            $table->foreignId('diverifikasi_oleh')->nullable()->after('alasan_penolakan')->constrained('users')->nullOnDelete();
            $table->timestamp('diverifikasi_at')->nullable()->after('diverifikasi_oleh');
        });
    }

    public function down(): void
    {
        Schema::table('shops', function (Blueprint $table) {
            $table->dropConstrainedForeignId('diverifikasi_oleh');
            $table->dropColumn(['alasan_penolakan', 'diverifikasi_at']);
        });
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('reviews', function (Blueprint $table) {
            $table->timestamp('disembunyikan_at')->nullable()->after('dibalas_at');
            $table->string('alasan_moderasi', 300)->nullable()->after('disembunyikan_at');
            $table->foreignId('dimoderasi_oleh')->nullable()->after('alasan_moderasi')->constrained('users')->nullOnDelete();

            $table->index(['product_id', 'disembunyikan_at']);
            $table->index(['shop_id', 'disembunyikan_at']);
        });
    }

    public function down(): void
    {
        Schema::table('reviews', function (Blueprint $table) {
            $table->dropIndex(['product_id', 'disembunyikan_at']);
            $table->dropIndex(['shop_id', 'disembunyikan_at']);
            $table->dropConstrainedForeignId('dimoderasi_oleh');
            $table->dropColumn(['disembunyikan_at', 'alasan_moderasi']);
        });
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('shops');
        Schema::dropIfExists('kelurahans');

        Schema::create('kelompoks', function (Blueprint $table) {
            $table->id();
            $table->string('nama');
            $table->string('desa')->nullable();
            $table->string('kecamatan')->nullable();
            $table->timestamps();
            $table->unique(['nama', 'desa', 'kecamatan']);
        });

        Schema::create('shops', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('kelompok_id')->constrained('kelompoks');
            $table->string('nama', 120);
            $table->string('status', 20)->default('menunggu');
            $table->timestamps();
        });

        Schema::table('users', function (Blueprint $table) {
            $table->unsignedBigInteger('kelompok_id')->nullable()->after('role');
        });

        DB::table('users')->where('role', 'admin_kelurahan')->update(['role' => 'admin_kelompok']);
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('kelompok_id');
        });

        Schema::dropIfExists('shops');
        Schema::dropIfExists('kelompoks');

        Schema::create('kelurahans', function (Blueprint $table) {
            $table->id();
            $table->string('nama')->unique();
            $table->timestamps();
        });

        Schema::create('shops', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('kelurahan_id')->constrained('kelurahans');
            $table->string('nama', 120);
            $table->string('status', 20)->default('menunggu');
            $table->timestamps();
        });
    }
};

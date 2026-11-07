<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('kelompoks', function (Blueprint $table) {
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
        });

        Schema::create('kegiatans', function (Blueprint $table) {
            $table->id();
            $table->string('judul', 150);
            $table->text('deskripsi');
            $table->string('lokasi', 150)->nullable();
            $table->dateTime('mulai_at')->index();
            $table->dateTime('selesai_at')->nullable();
            $table->boolean('dipublikasikan')->default(true);
            $table->timestamps();
        });

        Schema::create('panduans', function (Blueprint $table) {
            $table->id();
            $table->string('judul', 150);
            $table->string('kategori', 20)->index();
            $table->text('isi');
            $table->unsignedSmallInteger('urutan')->default(0);
            $table->boolean('dipublikasikan')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('panduans');
        Schema::dropIfExists('kegiatans');

        Schema::table('kelompoks', function (Blueprint $table) {
            $table->dropColumn(['latitude', 'longitude']);
        });
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('documentation_entries', function (Blueprint $table) {
            $table->id();
            $table->string('class_name')->unique(); // Pastikan nama kelas unik
            $table->text('source_path');
            $table->json('metadata')->nullable(); // Untuk menyimpan ringkasan JSON
            $table->longText('documentation'); // Dokumentasi yang dijana OpenAI
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('documentation_entries');
    }
};
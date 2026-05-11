<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    public function up(): void
    {
        Schema::create('test_results', function(Blueprint $table): void
        {
            $table->id();
            $table->foreignId('order_id')->constrained()->restrictOnDelete();
            $table->string('name');
            $table->string('value');
            $table->string('reference');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('test_results');
    }
};

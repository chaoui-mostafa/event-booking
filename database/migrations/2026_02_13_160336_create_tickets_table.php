<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tickets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_id')->constrained()->onDelete('cascade');
            $table->string('name');
            $table->text('description')->nullable();
            $table->decimal('price', 10, 2);
            $table->integer('quantity');
            $table->integer('available_quantity');
            $table->dateTime('sales_end_at')->nullable();
            $table->timestamps();

            $table->index(['event_id', 'available_quantity']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tickets');
    }
};

<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('carpool_stations', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->decimal('latitude', 10,7);
            $table->decimal('longitude', 10,7);
            $table->uuid('zone_id')->nullable(); // <-- تعديل هنا لدعم UUID
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('carpool_stations');
    }
};
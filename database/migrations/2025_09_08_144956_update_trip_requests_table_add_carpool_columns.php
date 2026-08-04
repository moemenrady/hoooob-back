<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('trip_requests', function (Blueprint $table) {
            if (!Schema::hasColumn('trip_requests', 'ride_type')) {
                $table->string('ride_type')->nullable();
            }
            if (!Schema::hasColumn('trip_requests', 'gender')) {
                $table->enum('gender', ['male', 'female', 'any'])->nullable();
            }
            if (!Schema::hasColumn('trip_requests', 'is_carpool')) {
                $table->boolean('is_carpool')->default(false);
            }
            if (!Schema::hasColumn('trip_requests', 'carpool_route_id')) {
                $table->unsignedBigInteger('carpool_route_id')->nullable();
                $table->foreign('carpool_route_id')
                    ->references('id')
                    ->on('carpool_routes')
                    ->onDelete('set null');
            }
            if (!Schema::hasColumn('trip_requests', 'required_seats')) {
                $table->integer('required_seats')->default(1);
            }
            if (!Schema::hasColumn('trip_requests', 'carpool_ride_location')) {
                $table->point('carpool_ride_location')->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::table('trip_requests', function (Blueprint $table) {
            if (Schema::hasColumn('trip_requests', 'carpool_route_id')) {
                $table->dropForeign(['carpool_route_id']);
            }

            $columns = [
                'ride_type',
                'gender',
                'is_carpool',
                'carpool_route_id',
                'required_seats',
                'carpool_ride_location',
            ];

            foreach ($columns as $column) {
                if (Schema::hasColumn('trip_requests', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};



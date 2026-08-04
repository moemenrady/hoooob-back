<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('carpool_routes', function (Blueprint $table) {
            $table->text('encoded_polyline')->nullable()->after('route_points');
        });
    }

    public function down(): void
    {
        Schema::table('carpool_routes', function (Blueprint $table) {
            $table->dropColumn('encoded_polyline');
        });
    }
};


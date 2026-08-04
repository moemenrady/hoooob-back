<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('trip_requests', function (Blueprint $table) {
            $table->timestamp('started_at')->nullable()->after('carpool_ride_location');
            $table->timestamp('ended_at')->nullable()->after('started_at');
            $table->string('status')->default('pending')->after('ended_at');
        });
    }

    public function down(): void
    {
        Schema::table('trip_requests', function (Blueprint $table) {
            $table->dropColumn(['started_at', 'ended_at', 'status']);
        });
    }
};


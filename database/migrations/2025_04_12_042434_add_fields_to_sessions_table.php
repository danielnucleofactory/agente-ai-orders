<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('sessions', function (Blueprint $table) {
            $table->string('device_type')->nullable();
            $table->string('country')->nullable();
            $table->boolean('is_active')->default(true);
        });
    }

    public function down()
    {
        Schema::table('sessions', function (Blueprint $table) {
            $table->dropColumn(['device_type', 'country', 'is_active']);
        });
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('purchase_order_comments', function (Blueprint $table) {
            $table->string('operacion')->nullable()->after('comment');
        });
    }

    public function down()
    {
        Schema::table('purchase_order_comments', function (Blueprint $table) {
            $table->dropColumn('operacion');
        });
    }
};

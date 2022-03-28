<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ChangeColumToOrdersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->foreignId('store_id')->nullable()->change();
            $table->foreignId('parcel_category_id')->nullable();
            $table->json('receiver_details')->nullable();
            $table->enum('charge_payer',['sender','receiver'])->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->foreignId('store_id')->change();
            $table->dropColumn('parcel_category_id');
            $table->dropColumn('receiver_details');
            $table->dropColumn('charge_payer');
        });
    }
}

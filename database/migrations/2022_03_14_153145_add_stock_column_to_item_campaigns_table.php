<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddStockColumnToItemCampaignsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('item_campaigns', function (Blueprint $table) {
            $table->foreignId('module_id')->constrained('modules');
            $table->integer('stock')->nullable()->default(0);
            $table->foreignId('unit_id')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('item_campaigns', function (Blueprint $table) {
            $table->dropColumn('stock');
            $table->dropColumn('unit_id');
            $table->dropColumn('module_id');
        });
    }
}

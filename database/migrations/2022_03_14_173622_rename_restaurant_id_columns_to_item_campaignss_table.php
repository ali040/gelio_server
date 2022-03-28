<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class RenameRestaurantIdColumnsToItemCampaignssTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('item_campaigns', function (Blueprint $table) {
            $table->renameColumn('restaurant_id','store_id');
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
            //
        });
    }
}

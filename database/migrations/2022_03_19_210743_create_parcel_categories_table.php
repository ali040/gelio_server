<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateParcelCategoriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('parcel_categories', function (Blueprint $table) {
            $table->id();
            $table->string('image', 191)->nullable();
            $table->string('name',191)->unique();
            $table->text('description');
            $table->boolean('status')->default(true);
            $table->integer('orders_count')->default(0);
            $table->foreignId('module_id')->constrained('modules');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('parcel_categories');
    }
}

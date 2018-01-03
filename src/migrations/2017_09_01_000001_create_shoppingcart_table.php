<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateShoppingCartTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('shoppingcarts', function (Blueprint $table) {
            $table->primary('id');

            $table->string('identifier');
            $table->string('name');

            $table->longText('content')->nullable()->default(null);

            $table->timestamps();

            $table->unique(['identifier', 'name']);
        });
    }
    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::drop('shoppingcarts');
    }
}

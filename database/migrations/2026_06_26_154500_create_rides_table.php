<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRidesTable extends Migration
{
    public function up()
    {
        Schema::create('rides', function (Blueprint $table) {

		$table->bigIncrements('id')->unsigned();
		$table->string('ride',10);
		$table->string('ride_desc',200)->nullable();
		$table->float('distance_k', 10, 2)->nullable();
		$table->float('distance_miles', 10, 2)->nullable();
		$table->timestamp('created_at')->nullable()->default('NULL');
		$table->timestamp('updated_at')->nullable()->default('NULL');
        });
    }

    public function down()
    {
        Schema::dropIfExists('rides');
    }
}



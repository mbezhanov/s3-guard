<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateHostsTable extends Migration
{
    public function up()
    {
        Schema::create('hosts', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name')->unique();
            $table->string('username');
            $table->string('password');
            $table->string('access_key', 20);
            $table->string('secret_key', 40);
            $table->string('bucket_name');
            $table->string('region_name');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('hosts');
    }
}

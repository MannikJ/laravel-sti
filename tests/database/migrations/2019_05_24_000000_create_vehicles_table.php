<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

class CreateVehiclesTable extends Migration
{

    public function up(): void
    {
        Schema::create('vehicles', function ($table) {
            $table->increments('id');
            STI::column($table)->nullable();
            $table->string('name');
            $table->timestamps();
        });
    }
}

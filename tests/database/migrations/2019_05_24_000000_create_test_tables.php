<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

class CreateTestTables extends Migration
{

    public function up(): void
    {
        Schema::create('vehicles', function ($table) {
            $table->increments('id');
            STI::column($table)->nullable();
            $table->unsignedBigInteger('category_id')->nullable();
            $table->foreign('category_id')->references('id')->on('categories');
            $table->string('name');
            $table->timestamps();
        });
        Schema::create('categories', function ($table) {
            $table->increments('id');
            $table->string('name')->nullable();
            $table->string('class_name')->nullable();
            STI::column($table)->nullable();
            $table->timestamps();
        });
        Schema::create('supers', function ($table) {
            $table->increments('id');
            $table->string('name')->nullable();
            $table->unsignedBigInteger('category_id')->nullable();
            $table->foreign('category_id')->references('id')->on('categories');
            $table->timestamps();
        });
    }
}

<?php

namespace Database\Migrations;

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Schema table name to migrate
     * @var string
     */
    public $tableName = 'locations_translations';

    /**
     * Run the migrations.
     * @table locations_translations
     *
     * @return void
     */
    public function up()
    {
        Schema::create($this->tableName, function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->increments('id');
            $table->string('lang', 3);
            $table->string('name');
            $table->string('slug')->nullable();
            $table->unsignedInteger('location_id');

            $table->index(["location_id"], 'fk_countries_translations_countries1_idx');
            $table->softDeletes();
            $table->nullableTimestamps();


            $table->foreign('location_id', 'fk_countries_translations_countries1_idx')
                ->references('id')->on('locations')
                ->onDelete('no action')
                ->onUpdate('no action');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists($this->tableName);
    }
};

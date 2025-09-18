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
    public $tableName = 'miracles_locations';

    /**
     * Run the migrations.
     * @table miracles_locations
     *
     * @return void
     */
    public function up()
    {
        Schema::create($this->tableName, function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->unsignedInteger('miracle_id');
            $table->unsignedInteger('location_id');

            $table->index(["location_id"], 'fk_miracles_has_countries_countries1_idx');

            $table->index(["miracle_id"], 'fk_miracles_has_countries_miracles1_idx');


            $table->foreign('miracle_id', 'fk_miracles_has_countries_miracles1_idx')
                ->references('id')->on('miracles')
                ->onDelete('no action')
                ->onUpdate('no action');

            $table->foreign('location_id', 'fk_miracles_has_countries_countries1_idx')
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

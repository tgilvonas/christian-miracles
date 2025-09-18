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
    public $tableName = 'persons_locations';

    /**
     * Run the migrations.
     * @table persons_locations
     *
     * @return void
     */
    public function up()
    {
        Schema::create($this->tableName, function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->unsignedInteger('person_id');
            $table->unsignedInteger('location_id');

            $table->index(["location_id"], 'fk_persons_has_countries_countries1_idx');

            $table->index(["person_id"], 'fk_persons_has_countries_persons1_idx');


            $table->foreign('person_id', 'fk_persons_has_countries_persons1_idx')
                ->references('id')->on('persons')
                ->onDelete('no action')
                ->onUpdate('no action');

            $table->foreign('location_id', 'fk_persons_has_countries_countries1_idx')
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

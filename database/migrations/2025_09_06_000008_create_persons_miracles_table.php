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
    public $tableName = 'persons_miracles';

    /**
     * Run the migrations.
     * @table persons_miracles
     *
     * @return void
     */
    public function up()
    {
        Schema::create($this->tableName, function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->unsignedInteger('person_id');
            $table->unsignedInteger('miracle_id');

            $table->index(["miracle_id"], 'fk_persons_has_miracles_miracles1_idx');

            $table->index(["person_id"], 'fk_persons_has_miracles_persons_idx');


            $table->foreign('person_id', 'fk_persons_has_miracles_persons_idx')
                ->references('id')->on('persons')
                ->onDelete('no action')
                ->onUpdate('no action');

            $table->foreign('miracle_id', 'fk_persons_has_miracles_miracles1_idx')
                ->references('id')->on('miracles')
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

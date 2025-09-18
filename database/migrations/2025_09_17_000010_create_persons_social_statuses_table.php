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
    public $tableName = 'persons_social_statuses';

    /**
     * Run the migrations.
     * @table persons_social_statuses
     *
     * @return void
     */
    public function up()
    {
        Schema::create($this->tableName, function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->unsignedInteger('persons_id');
            $table->unsignedInteger('social_statuses_id');

            $table->index(["social_statuses_id"], 'fk_persons_has_social_statuses_social_statuses1_idx');

            $table->index(["persons_id"], 'fk_persons_has_social_statuses_persons1_idx');


            $table->foreign('persons_id', 'fk_persons_has_social_statuses_persons1_idx')
                ->references('id')->on('persons')
                ->onDelete('no action')
                ->onUpdate('no action');

            $table->foreign('social_statuses_id', 'fk_persons_has_social_statuses_social_statuses1_idx')
                ->references('id')->on('social_statuses')
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

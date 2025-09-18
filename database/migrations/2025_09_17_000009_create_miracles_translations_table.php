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
    public $tableName = 'miracles_translations';

    /**
     * Run the migrations.
     * @table miracles_translations
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
            $table->text('meta_description')->nullable();
            $table->text('meta_keywords')->nullable();
            $table->longText('description');
            $table->unsignedInteger('miracle_id');

            $table->index(["miracle_id"], 'fk_miracles_translations_miracles1_idx');
            $table->softDeletes();
            $table->nullableTimestamps();


            $table->foreign('miracle_id', 'fk_miracles_translations_miracles1_idx')
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

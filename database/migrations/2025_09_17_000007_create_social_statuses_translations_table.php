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
    public $tableName = 'social_statuses_translations';

    /**
     * Run the migrations.
     * @table social_statuses_translations
     *
     * @return void
     */
    public function up()
    {
        Schema::create($this->tableName, function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->increments('id');
            $table->string('lang', 3)->nullable();
            $table->string('name')->nullable();
            $table->string('slug')->nullable();
            $table->text('meta_keywords')->nullable();
            $table->text('meta_description')->nullable();
            $table->text('description')->nullable();
            $table->unsignedInteger('social_status_id');

            $table->index(["social_status_id"], 'fk_social_statuses_translations_social_statuses1_idx');
            $table->softDeletes();
            $table->nullableTimestamps();


            $table->foreign('social_status_id', 'fk_social_statuses_translations_social_statuses1_idx')
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

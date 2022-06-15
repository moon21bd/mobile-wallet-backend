<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateHttpLogsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('http_logs', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('log_name', 255)->nullable();
            $table->string('requester_ref_id', 255)->nullable();
            $table->string('receiver_ref_id', 255)->nullable();
            $table->string('from_url', 500)->nullable();
            $table->string('to_url', 500)->nullable();
            $table->string('method', 20)->nullable();
            $table->longText('request')->nullable();
            $table->longText('response')->nullable();
            $table->string('status_code', 20)->nullable();
            $table->string('direction', 20)->nullable();
            $table->unsignedDouble('response_time')->nullable();
            $table->integer('created_by')->nullable();
            $table->integer('updated_by')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('http_logs');
    }
}

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEwSmsHistoriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ew_sms_histories', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_unicode_ci';
            //table colum
            $table->id();
            $table->string('mobile_no',32);
            $table->string('sms_provider',64);
            $table->text('request_data');
            $table->text('response_data');
            $table->enum('status', ['sent', 'failed']);
            $table->tinyInteger('is_synchronized')->default(0);
            $table->text('organization_ref_id')->nullable();
            $table->text('user_ref_id')->nullable();
            $table->text('role_ref_id')->nullable();
            $table->text('department_ref_id')->nullable();
            $table->integer('created_by')->default(0);
            $table->integer('updated_by')->default(0);
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
        Schema::dropIfExists('ew_sms_histories');
    }
}

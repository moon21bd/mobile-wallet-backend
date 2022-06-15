<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEWBankTransferRequestsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('ew_bank_transfer_requests')) {
            Schema::create('ew_bank_transfer_requests', function (Blueprint $table) {
                $table->engine    = 'InnoDB';
                $table->charset   = 'utf8mb4';
                $table->collation = 'utf8mb4_unicode_ci';
                //table colum description
                $table->id();
                $table->uuid('transfer_request_id')->index();
                $table->integer('sender_id')->index();
                $table->integer('receiver_id')->index();
                $table->string('account_number', '128')->nullable();
                $table->string('account_name', '255')->nullable();
                $table->integer('trx_activity_type');
                $table->double('trx_amount');
                $table->string('trx_currency', '32')->nullable();
                $table->text('pgw_request')->nullable();
                $table->text('pgw_response')->nullable();
                $table->string('trx_note', '255')->nullable();
                $table->enum('trx_status',['pending','success','cancel'])->default('pending')->index();
                //platform framework colum
                $table->text('organization_ref_id')->nullable();
                $table->text('user_ref_id')->nullable();
                $table->text('role_ref_id')->nullable();
                $table->text('department_ref_id')->nullable();
                $table->integer('created_by')->default(0);
                $table->integer('updated_by')->default(0);

                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('ew_bank_transfer_requests');
    }
}

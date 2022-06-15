<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEWBankTrxRequestsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('ew_bank_trx_requests')) {
            Schema::create('ew_bank_trx_requests', function (Blueprint $table) {
                $table->engine = 'InnoDB';
                $table->charset = 'utf8mb4';
                $table->collation = 'utf8mb4_unicode_ci';
                //table colum description
                $table->id();
                $table->uuid('trx_id');
                $table->string('trx_ref_id', '64');
                $table->integer('sender_id')->index();
                $table->integer('receiver_id')->index();
                $table->integer('trx_activity_type');
                $table->string('wallet_id', '64');
                $table->double('trx_amount');
                $table->string('trx_currency', '32')->nullable();
                $table->string('trx_note', '255')->nullable();
                $table->enum('trx_status',['pending','success','cancel'])->default('pending');
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
        Schema::dropIfExists('ew_bank_trx_requests');
    }
}

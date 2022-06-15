<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterEwBankTransferRequest extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('ew_bank_transfer_requests', function (Blueprint $table) {
            DB::statement("ALTER TABLE `ew_bank_transfer_requests` MODIFY `trx_status` ENUM('pending','success','cancel','failed') NOT NULL DEFAULT 'pending'");
            if(!Schema::hasColumn('ew_bank_transfer_requests','payment_id'))
            $table->string('payment_id', '255')->nullable()->after('trx_currency');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('ew_bank_transfer_requests', function (Blueprint $table) {
            DB::statement("ALTER TABLE `ew_bank_transfer_requests` MODIFY `trx_status` ENUM('pending','success','cancel') NOT NULL DEFAULT 'pending'");
            if(Schema::hasColumn('ew_bank_transfer_requests','payment_id'))
                $table->dropColumn('payment_id');
        });
    }
}

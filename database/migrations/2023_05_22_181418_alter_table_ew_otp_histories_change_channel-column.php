<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AlterTableEwOtpHistoriesChangeChannelColumn extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('ew_otp_histories', function (Blueprint $table) {
            DB::statement("ALTER TABLE ew_otp_histories MODIFY channel ENUM('register', 'forgot', 'transaction') NOT NULL DEFAULT 'transaction' after smsgw_response");
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('ew_otp_histories', function (Blueprint $table) {
            DB::statement("ALTER TABLE ew_otp_histories MODIFY channel ENUM('register', 'forgot') NOT NULL DEFAULT 'transaction' after smsgw_response");
        });
    }
}

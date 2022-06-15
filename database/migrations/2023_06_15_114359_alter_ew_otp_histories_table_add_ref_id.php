<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterEwOtpHistoriesTableAddRefId extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('ew_otp_histories', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_unicode_ci';
            //add ref id
            if(!Schema::hasColumn('ew_otp_histories','ref_id'))
                $table->uuid('ref_id')->nullable()->after('channel');
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
            if(Schema::hasColumn('ew_otp_histories','ref_id'))
                $table->dropColumn('ref_id');
        });
    }
}

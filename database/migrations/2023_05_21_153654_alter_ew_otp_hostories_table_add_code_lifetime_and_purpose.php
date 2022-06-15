<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterEwOtpHostoriesTableAddCodeLifetimeAndPurpose extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('ew_otp_histories', function (Blueprint $table) {
            $table->datetime('code_lifetime')->nullable();
            $table->enum('type', ['regular', 'resend'])->default('regular');
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
            $table->dropColumn(['code_lifetime', 'type']);
        });
    }
}

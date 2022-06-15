<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterFwPasswordChangeRequestsTableAddType extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('fw_password_change_requests', function (Blueprint $table) {
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
        Schema::table('fw_password_change_requests', function (Blueprint $table) {
            $table->dropColumn('type');
        });
    }
}

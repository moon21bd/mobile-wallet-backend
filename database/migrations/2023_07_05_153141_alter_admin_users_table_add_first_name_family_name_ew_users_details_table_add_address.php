<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterAdminUsersTableAddFirstNameFamilyNameEwUsersDetailsTableAddAddress extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Add columns to admin_users table
        Schema::table('admin_users', function (Blueprint $table) {
            $table->string('first_name')->nullable()->after('name');
            $table->string('family_name')->nullable()->after('first_name');
        });

        // Add column to ew_user_details table
        Schema::table('ew_user_details', function (Blueprint $table) {
            $table->string('address')->nullable()->after('country_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Remove columns from admin_users table
        Schema::table('admin_users', function (Blueprint $table) {
            $table->dropColumn('first_name');
            $table->dropColumn('family_name');
        });

        // Remove column from ew_user_details table
        Schema::table('ew_user_details', function (Blueprint $table) {
            $table->dropColumn('address');
        });
    }
}

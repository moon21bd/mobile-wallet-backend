<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterEwCountries extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('ew_countries', function (Blueprint $table) {
            if(!Schema::hasColumn('ew_countries','name_en'))
                $table->string('name_en', '255')->nullable()->after('name');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('ew_countries', function (Blueprint $table) {
            if(Schema::hasColumn('ew_countries','name_en'))
                $table->dropColumn('name_en');
        });
    }
}

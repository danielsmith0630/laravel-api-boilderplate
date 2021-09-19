<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddUserFieldsToUserPrivacySettingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('user_privacy_settings', function (Blueprint $table) {
            $table->softDeletes();
            $table->integer('created_by')->unsigned()->after('created_at');
            $table->integer('updated_by')->unsigned()->after('updated_at');
            $table->integer('deleted_by')->unsigned()->nullable()->after('deleted_at');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('user_privacy_settings', function (Blueprint $table) {
            $table->dropSoftDeletes();
            $table->dropColumn(['created_by', 'updated_by', 'deleted_by']);
        });
    }
}

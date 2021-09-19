<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddPublicMessagesToUserPrivacySettingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('user_privacy_settings', function (Blueprint $table) {
            $table->boolean('public_messages')->default(0)->after('is_public');
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
            $table->dropColumn('public_messages');
        });
    }
}

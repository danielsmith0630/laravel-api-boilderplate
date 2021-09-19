<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateTablesUserAt extends Migration
{
    protected $tables = [
      'users',
      'user_profiles',
      'user_settings',
      'spaces',
      'files',
      'space_members',
      'avatars',
      'banners',
      'channels',
      'channel_members'
    ];

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
      foreach ($this->tables as $table) {
        Schema::table($table, function (Blueprint $table) {
          $table->integer('created_by')->unsigned()->after('created_at');
          $table->integer('updated_by')->unsigned()->after('updated_at');
          $table->integer('deleted_by')->unsigned()->nullable()->after('deleted_at');
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
      foreach ($this->tables as $table) {
        if (Schema::hasColumn($table, 'created_by')) {
          Schema::table($table, function($table) {
            $table->dropColumn('created_by');
          });
        }
        if (Schema::hasColumn($table, 'updated_by')) {
          Schema::table($table, function($table) {
            $table->dropColumn('updated_by');
          });
        }
        if (Schema::hasColumn($table, 'deleted_by')) {
          Schema::table($table, function($table) {
            $table->dropColumn('deleted_by');
          });
        }
      }
    }
}

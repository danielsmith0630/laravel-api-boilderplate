<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateRoleTypeInSpaceMembersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('space_members', function (Blueprint $table) {
            // \DB::statement("ALTER TABLE space_members CHANGE COLUMN role role ENUM('owner', 'admin', 'moderator', 'member') NOT NULL DEFAULT 'member'");
            $table->string('role', 30)->default('member')->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('space_members', function (Blueprint $table) {
            \DB::statement("ALTER TABLE space_members CHANGE COLUMN role role ENUM('admin', 'moderator', 'member') NOT NULL DEFAULT 'member'");
        });
    }
}

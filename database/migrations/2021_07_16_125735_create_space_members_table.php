<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSpaceMembersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('space_members', function (Blueprint $table) {
            $table->id();
            $table->foreignId('space_id')->constrained();
            $table->foreignId('user_id')->constrained();
            $table->string('title')->nullable();
            $table->enum('role', ['admin', 'moderator', 'member'])->default('member');
            $table->string('phone_number', 20)->nullable();
            $table->boolean('space_visibility')->default(true);
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['space_id', 'user_id', 'deleted_at']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('space_members');
    }
}

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFilesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('files', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained();
            $table->foreignId('space_id')->constrained()->nullable();
            $table->string('name')->nullable();
            $table->string('extension')->nullable();
            $table->string('mime')->nullable();
            $table->integer('size')->nullable();
            $table->text('path');
            $table->unsignedBigInteger('file_type_id');
            $table->string('file_type');
            $table->timestamps();
            $table->softDeletes();

            $table->index(['file_type', 'file_type_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('files');
    }
}

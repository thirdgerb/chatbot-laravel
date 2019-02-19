<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Commune\Chatbot\Laravel\Database\FieldSchema;

class CreateChatbotContextsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('chatbot_contexts', function (Blueprint $table) {

            $table->increments('id');

            FieldSchema::contextId($table);

            $table->binary('context_data');

            $table->timestamps();

            $table->unique('context_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('chatbot_contexts');
    }
}

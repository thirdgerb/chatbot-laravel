<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Commune\Chatbot\Laravel\Database\FieldSchema;

class CreateChatbotContextLogsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('chatbot_context_logs', function (Blueprint $table) {
            $table->increments('id');

            FieldSchema::contextId($table);

            FieldSchema::messageId($table);

            FieldSchema::chatId($table);

            FieldSchema::sessionId($table);

            FieldSchema::userId($table);

            FieldSchema::recipientId($table);

            FieldSchema::platformId($table);

            $table->binary('context_data');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('chatbot_context_logs');
    }
}

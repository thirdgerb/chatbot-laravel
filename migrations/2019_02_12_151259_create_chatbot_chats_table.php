<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Commune\Chatbot\Laravel\Database\FieldSchema;

class CreateChatbotChatsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('chatbot_chats', function (Blueprint $table) {
            $table->increments('id');

            FieldSchema::chatId($table);

            FieldSchema::userId($table);

            FieldSchema::recipientId($table);

            FieldSchema::platformId($table);

            $table->timestamps();

            $table->unique('chat_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('chatbot_chats');
    }
}

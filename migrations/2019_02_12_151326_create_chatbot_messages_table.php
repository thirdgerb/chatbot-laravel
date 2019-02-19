<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Commune\Chatbot\Laravel\Database\FieldSchema;

class CreateChatbotMessagesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('chatbot_messages', function (Blueprint $table) {
            $table->increments('id');

            FieldSchema::messageId($table);

            FieldSchema::messageId($table, 'incoming_');

            FieldSchema::chatId($table);

            FieldSchema::sessionId($table);

            FieldSchema::userId($table);

            FieldSchema::recipientId($table);

            FieldSchema::platformId($table);

            $table->string('message_body', 5000)
                ->default('');

            $table->timestamps();

            $table->unique('message_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('chatbot_messages');
    }
}

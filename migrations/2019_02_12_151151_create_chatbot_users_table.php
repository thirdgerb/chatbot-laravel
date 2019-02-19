<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Commune\Chatbot\Laravel\Database\FieldSchema;

class CreateChatbotUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('chatbot_users', function (Blueprint $table) {
            $table->increments('id');

            FieldSchema::userId($table);

            $table->string('name', 200)
                ->default('')
                ->comment('origin platform id');

            $table->string('avatar', 200)
                ->default('');

            $table->string('platform_id', 60)
                ->default('');

            $table->boolean('is_supervisor')
                ->default(0);

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
        Schema::dropIfExists('chatbot_users');
    }
}

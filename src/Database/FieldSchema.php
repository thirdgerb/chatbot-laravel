<?php

/**
 * Class FieldSchema
 * @package Commune\Chatbot\Laravel\Database
 */

namespace Commune\Chatbot\Laravel\Database;


use Illuminate\Database\Schema\Blueprint;

class FieldSchema
{
    public static function messageId(Blueprint $table, $prefix = '')
    {
        $table->string($prefix . 'message_id', 60)->default('');
    }

    public static function contextId(Blueprint $table)
    {
        $table->string('context_id', 60)->default('');
    }
    
    public static function chatId(Blueprint $table)
    {
        $table->string('chat_id', 60)->default('');
    }

    public static function sessionId(Blueprint $table)
    {
        $table->string('session_id', 60)->default('');
    }

    public static function userId(Blueprint $table)
    {
        $table->string('user_id', 60)->default('');
    }


    public static function recipientId(Blueprint $table)
    {
        $table->string('recipient_id', 60)->default('');
    }


    public static function platformId(Blueprint $table)
    {
        $table->string('platform_id', 60)->default('');
    }




}
<?php

/**
 * Class FieldSchema
 * @package Commune\Chatbot\Laravel\Database
 * @author BrightRed
 */

namespace Commune\Chatbot\Laravel\Database;


use Commune\Chatbot\OOHost\Session\Session;
use Illuminate\Database\Schema\Blueprint;

class TableSchema
{
    const CONTEXTS_TABLE = 'chatbot_contexts';
    const BREAKPOINTS_TABLE = 'chatbot_breakpoints';
    const SESSION_DATA_TABLE = 'chatbot_session_data';

    public static function scope(Blueprint $table) : void
    {
        TableSchema::id('session_id', $table);
        TableSchema::id('chat_id', $table);
        TableSchema::id('user_id', $table);
        TableSchema::id('platform_id', $table, 100);
        TableSchema::id('chatbot_user_id', $table);
        TableSchema::id('conversation_id', $table);
    }
    
    public static function getScopeFromSession(Session $session) : array
    {
        $scope = $session->scope;
        return [
            'session_id' => $scope->sessionId,
            'chat_id' => $scope->chatId,
            'user_id' => $scope->userId,
            'platform_id' => $scope->platformId,
            'chatbot_user_id' => $scope->chatbotUserId,
            'conversation_id' => $scope->conversationId,
        ];
    }

    public static function id(string $name, Blueprint $table, int $length = 40) : void
    {
        $table->string($name, $length)
            ->comment("id类型 $name 字段")
            ->default('');
    }

}
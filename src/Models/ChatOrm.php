<?php

/**
 * Class Chat
 * @package Commune\Chatbot\Laravel\Models
 */

namespace Commune\Chatbot\Laravel\Models;


use Commune\Chatbot\Framework\Conversation\Conversation;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Query\Builder;

/**
 * Class ChatOrm
 * @mixin Builder
 * @package Commune\Chatbot\Laravel\Models
 */
class ChatOrm extends Model
{
    protected $table = 'chatbot_chats';

    protected $connection = CHATBOT_MODEL_DRIVER;

    protected $fillable = [
        'chat_id',
        'user_id',
        'recipient_id',
        'platform_id',
    ];

    public static function createByConversation(Conversation $conversation) : self
    {
        $chat =  (new ChatOrm([
            'chat_id' => $conversation->getChatId(),
            'user_id' => $conversation->getSender()->getId(),
            'recipient_id' => $conversation->getRecipient()->getId(),
            'platform_id' => $conversation->getPlatform()->getId(),
        ]));
        $chat->save();
        return $chat;
    }

}
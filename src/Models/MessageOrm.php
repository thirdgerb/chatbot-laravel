<?php

/**
 * Class UserModel
 * @package Commune\Chatbot\Laravel\Models
 */

namespace Commune\Chatbot\Laravel\Models;


use Illuminate\Database\Eloquent\Model;

class MessageOrm extends Model
{
    protected $table = 'chatbot_messages';

    protected $connection = CHATBOT_MODEL_DRIVER;

    protected $fillable = [
        'chat_id',
        'session_id',
        'message_id',
        'incoming_message_id',
        'user_id',
        'recipient_id',
        'platform_id',
        'message_text',
        'message_body',
    ];
}
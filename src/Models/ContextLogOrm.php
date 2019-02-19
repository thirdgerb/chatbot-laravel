<?php

/**
 * Class ContextLogOrm
 * @package Commune\Chatbot\Laravel\Models
 */

namespace Commune\Chatbot\Laravel\Models;


use Illuminate\Database\Eloquent\Model;

class ContextLogOrm extends Model
{
    protected $table = 'chatbot_context_logs';

    protected $connection = CHATBOT_MODEL_DRIVER;

    protected $fillable = [
        'context_id',
        'message_id',
        'chat_id',
        'session_id',
        'user_id',
        'recipient_id',
        'platform_id',
        'context_data',
    ];

}
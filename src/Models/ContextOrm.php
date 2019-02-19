<?php

/**
 * Class ContextOrm
 * @package Commune\Chatbot\Laravel\Models
 */

namespace Commune\Chatbot\Laravel\Models;


use Commune\Chatbot\Framework\Context\ContextData;
use Illuminate\Database\Eloquent\Model;

class ContextOrm extends Model
{
    protected $table = 'chatbot_contexts';

    protected $connection = CHATBOT_MODEL_DRIVER;

    protected $fillable = [
        'context_id',
        'context_data',
    ];


    public static function serializeContextData(ContextData $data) : string
    {
        return serialize($data);
    }

    public static function unserializeContextdata(string $data) : ContextData
    {
        return unserialize($data) ? : null;
    }
}
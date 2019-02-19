<?php

/**
 * Class UserModel
 * @package Commune\Chatbot\Laravel\Models
 */

namespace Commune\Chatbot\Laravel\Models;


use Illuminate\Database\Eloquent\Model;

class UserOrm extends Model
{
    protected $table = 'chatbot_users';

    protected $connection = CHATBOT_MODEL_DRIVER;

}
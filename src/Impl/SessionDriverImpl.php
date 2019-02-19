<?php

/**
 * Class SessionDriver
 * @package Commune\Chatbot\Laravel\Impl
 */

namespace Commune\Chatbot\Laravel\Impl;


use Commune\Chatbot\Contracts\ChatbotApp;
use Commune\Chatbot\Contracts\SessionDriver;
use Commune\Chatbot\Framework\Context\ContextData;
use Commune\Chatbot\Framework\Conversation\Scope;
use Commune\Chatbot\Framework\Directing\History;
use Commune\Chatbot\Laravel\Models\ContextLogOrm;
use Commune\Chatbot\Laravel\Models\ContextOrm;
use Illuminate\Contracts\Redis\Connection;
use Illuminate\Support\Facades\Redis;

class SessionDriverImpl implements SessionDriver
{
    protected  $app;

    public function __construct(ChatbotApp $app)
    {
        $this->app = $app;
    }


    /**
     * @return \Redis
     */
    protected function redis() : Connection
    {
        return Redis::connection(CHATBOT_REDIS_DRIVER);
    }

    protected function sessionHistoryKey(string $sessionId) : string
    {
        return "chat:session:$sessionId:history";
    }

    public function fetchContextDataById(string $id): ? ContextData
    {
        $context = ContextOrm::query()->where('context_id', $id)->first(['context_data', 'context_id']);

        if ($context) {
            return ContextOrm::unserializeContextdata($context['context_data']);
        }

        return null;
    }

    public function saveContextData(ContextData $data)
    {
        $dataArr = [
            'context_id' => $data->getId(),
            'context_data' => $contextData = ContextOrm::serializeContextData($data)
        ];

        ContextOrm::query()->firstOrCreate(['context_id' => $data->getId()],$dataArr);

        (new ContextLogOrm([
            'context_id' => $data->getId(),
            'message_id' => $data->getScope()[Scope::MESSAGE],
            'chat_id' => $data->getScope()[Scope::CHAT],
            'session_id'  => $data->getScope()[Scope::SESSION],
            'user_id'  => $data->getScope()[Scope::SENDER],
            'recipient_id'  => $data->getScope()[Scope::RECIPIENT],
            'platform_id' => $data->getScope()[Scope::PLATFORM],
            'context_data' => $contextData,
        ]))->save();
    }

    public function loadHistory(string $sessionId): ? History
    {
        $key = $this->sessionHistoryKey($sessionId);
        $val = $this->redis()->get($key);

        //todo
        return !empty($val) ? unserialize($val) : null;
    }

    public function saveHistory(string $sessionId, History $history)
    {
        $key = $this->sessionHistoryKey($sessionId);
        $val = serialize($history);
        $expire = $this->app->getConfig(ChatbotAppImpl::REDIS_SESSION_EXPIRE, 3600);
        $r = $this->redis()->setex($key, $expire, $val);
        //todo
    }



}
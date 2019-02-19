<?php

/**
 * Class ChatDriverImpl
 * @package Commune\Chatbot\Laravel\Impl
 */

namespace Commune\Chatbot\Laravel\Impl;


use Commune\Chatbot\Contracts\ChatbotApp;
use Commune\Chatbot\Contracts\ChatDriver;
use Commune\Chatbot\Contracts\IdGenerator;
use Commune\Chatbot\Framework\Conversation\Conversation;
use Commune\Chatbot\Framework\Conversation\IncomingMessage;
use Commune\Chatbot\Framework\Exceptions\ChatbotException;
use Commune\Chatbot\Framework\Message\Message;
use Commune\Chatbot\Framework\Message\Text;
use Commune\Chatbot\Laravel\Models\ChatOrm;
use Commune\Chatbot\Laravel\Models\MessageOrm;
use Commune\Chatbot\Laravel\Repositories\ChatRep;
use Illuminate\Contracts\Redis\Connection;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Redis;

class ChatDriverImpl implements ChatDriver
{
    /**
     * @var ChatbotApp
     */
    protected $app;

    /**
     * @var IdGenerator
     */
    protected $idGenerator;

    /**
     * @var string
     */
    protected $redisDriver;
    

    public function __construct(ChatbotApp $app, IdGenerator $idGenerator)
    {
        $this->app = $app;
        $this->idGenerator = $idGenerator;
        
        $this->redisDriver = $this->app->getConfig(ChatbotAppImpl::REDIS_DRIVER);
    }
    /*------- components -------*/

    /**
     * @return \Redis
     */
    protected function redis() : Connection
    {
        return Redis::connection(CHATBOT_REDIS_DRIVER);
    }

    /*------- key ------*/

    protected function chatSessionIdKey(string $chatId) : string
    {
        return "chat:$chatId:sessionId";
    }
    
    protected function chatLockKey(string $chatId) : string
    {
        return "chat:locker:$chatId";
    }
    
    protected function chatMessageKey(string $chatId) : string
    {
        return "chat:$chatId:messages";
    }

    /*------- impl ------*/

    public function fetchIdOrCreateChat(Conversation $conversation): string
    {
        $chatId = $conversation->getChatId();

        $key = $this->chatSessionIdKey($chatId);

        if ($this->redis()->exists($key)) {
            return $chatId;
        }

        $exists = ChatOrm::query()->where('chat_id', $chatId)->exists();

        if ($exists) {
            return $chatId;
        }

        $chat = ChatOrm::createByConversation($conversation);

        return $chat['chat_id'];
    }


    public function fetchSessionIdOfChat(string $chatId): string
    {
        $redis = $this->redis();

        $key = $this->chatSessionIdKey($chatId);

        $id = $redis->get($key);

        if (!empty($id)) {
            return $id;
        }
        $id = $this->idGenerator->makeSessionId($chatId);

        if ($redis->setnx($key, $id)) {
            $ttl = $this->app->getConfig(ChatbotAppImpl::REDIS_SESSION_EXPIRE, 3600);
            $redis->expire($key, $ttl);
        } else {
            $id = $redis->get($key);
        }

        return $id;
    }

    public function closeSession(string $chatId)
    {
        $key = $this->chatSessionIdKey($chatId);
        $this->redis()->del($key);
    }

    public function chatIsTooBusy(string $chatId): bool
    {
        // todo not implements
        return false;
    }

    public function lockChat(string $chatId): bool
    {
        $key = $this->chatLockKey($chatId);
        if ($this->redis()->setnx($key, '1')) {
            $this->redis()->expire($key, $this->app->getConfig(ChatbotAppImpl::REDIS_CHAT_LOCKER_EXPIRE, 3));
            return true;
        }
        return false;
    }

    public function unlockChat(string $chatId)
    {
        $key = $this->chatLockKey($chatId);
        $this->redis()->del($key);
    }

    public function pushIncomingMessage(string $chatId, string $sessionId, IncomingMessage $message)
    {
        (new MessageOrm([
            'chat_id' => $chatId,
            'session_id' => $sessionId,
            'message_id' => $message->getId(),
            'user_id' => $message->getSender()->getId(),
            'recipient_id' => $message->getRecipient()->getId(),
            'platform_id' => $message->getPlatform()->getId(),
            'message_body'  => $message->getMessage()->toJson(),
        ]))->save();
        
        $key = $this->chatMessageKey($chatId);
        
        $this->redis()->lPush($key, serialize($message));
    }

    public function popIncomingMessage(string $chatId): ? IncomingMessage
    {
         
        $key = $this->chatMessageKey($chatId);
        
        $data = $this->redis()->rPop($key);
        
        return $data ? unserialize($data) : null;
    }

    public function saveReplies(Conversation $conversation)
    {
        $chatId = $conversation->getChatId();
        $sessionId = $conversation->getSessionId();
        $iId = $conversation->getIncomingMessage()->getId();
        $uid = $conversation->getSender()->getId();
        $rid = $conversation->getRecipient()->getId();
        $pid = $conversation->getPlatform()->getId();
        foreach ($conversation->getReplies() as $reply) {
            /**
             * @var Message $reply
             */
            (new MessageOrm([
                'chat_id' => $chatId,
                'session_id' => $sessionId,
                'incoming_message_id'=> $iId,
                'message_id' => $this->idGenerator->makeMessageUUId(),
                'user_id' => $uid,
                'recipient_id' => $rid,
                'platform_id' => $pid,
                'message_body'  => $reply->toJson(),
            ]))->save();
        }
    }

    public function flushAwaitIncomingMessages(string $chatId)
    {
        $key = $this->chatMessageKey($chatId);
        $this->redis()->del($key);
    }

    public function replyWhenTooBusy(): Message
    {
        return new Text('too busy');
    }

    public function replyWhenException(ChatbotException $e): Message
    {
        //todo 要用更好的办法
        return new Text('error occur');
    }


}
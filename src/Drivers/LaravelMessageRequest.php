<?php

/**
 * Class LaravelMessageRequestTrait
 * @package Commune\Chatbot\Laravel\Drivers
 */

namespace Commune\Chatbot\Laravel\Drivers;


use Commune\Chatbot\Blueprint\Conversation\ConversationMessage;
use Commune\Chatbot\Blueprint\Conversation\MessageRequest;
use Commune\Chatbot\Blueprint\Message\Message;
use Commune\Chatbot\Framework\Conversation\MessageRequestHelper;
use Commune\Support\Uuid\HasIdGenerator;
use Commune\Support\Uuid\IdGeneratorHelper;
use Predis\ClientInterface;
use Predis\Pipeline\Pipeline;

abstract class LaravelMessageRequest implements MessageRequest, HasIdGenerator
{
    use IdGeneratorHelper, MessageRequestHelper;


    /**
     * @var Message|mixed
     */
    protected $input;

    /**
     * @var ConversationMessage[]
     */
    protected $buffer = [];

    /**
     * @var LaravelDBDriver
     */
    protected $driver;

    /**
     * @var Message
     */
    protected $inputMessage;

    /**
     * @var string
     */
    protected $messageId;

    /**
     * @var bool
     */
    protected $render = false;

    /**
     * LaravelMessageRequest constructor.
     * @param Message|mixed $input
     */
    public function __construct($input)
    {
        $this->input = $input;
    }

    public function getInput()
    {
        return $this->input;
    }

    abstract protected function makeInputMessage() : Message;

    /**
     * @param ConversationMessage[] $messages
     */
    abstract protected function renderChatMessages(array $messages) : void;


    public function generateMessageId(): string
    {
        return $this->createUuId();
    }

    public function fetchMessageId(): string
    {
        return $this->messageId
            ?? $this->messageId = $this->generateMessageId();
    }

    public function fetchTraceId(): string
    {
        return $this->fetchMessageId();
    }

    public function fetchMessage(): Message
    {
        return $this->inputMessage
            ?? $this->inputMessage = (
                $this->input instanceof Message
                ? $this->input
                : $this->makeInputMessage()
            );
    }

    public function bufferConversationMessage(ConversationMessage $message): void
    {
        $this->buffer[] = $message;
    }


    protected function getDriver() : LaravelDBDriver
    {
        return $this->driver
            ?? $this->driver = $this->conversation->make(LaravelDBDriver::class);
    }

    protected function userMessageKey(string $userId) : string
    {
        return "commune:chatbot:messageTo:$userId";
    }


    public function flushChatMessages(): void
    {
        if (!empty($this->buffer)) {
            $this->bufferToCache($this->buffer);
            $this->buffer = [];
        }

        // 只渲染一次.
        if ($this->render) {
            return;
        }

        $cached = $this->fetchCachedMessages();
        if (!empty($cached)) {
            $this->renderChatMessages($cached);
        }
        $this->render = true;
    }


    /**
     * @param ConversationMessage[] $messages
     */
    protected function bufferToCache(array $messages) : void
    {
        if (empty($messages)) {
            return;
        }

        // 先把消息压到队列里.
        $this->getDriver()->getRedis()->pipeline(function($pipe) use ($messages){
            /**
             * @var ClientInterface $pipe
             */
            $values = [];
            foreach ($messages as $message) {
                $key = $this->userMessageKey($message->getUserId());
                $values[$key][] = serialize($message);
            }
            foreach ($values as $key => $send) {
                $pipe->lpush($key, $send);
            }
        });
    }

    /**
     * @return ConversationMessage[]
     */
    protected function fetchCachedMessages() : array
    {
        $key = $this->userMessageKey($this->fetchUserId());
        $result = $this->getDriver()->getRedis()->pipeline(function($pipe) use ($key){
            /**
             * @var Pipeline $pipe
             */
            $pipe->lrange($key, 0, -1);
            $pipe->del([$key]);
        });

        $list = $result[0];
        if (empty($list)) {
            return [];
        }


        // 需要 render 的消息
        $rendering = [];
        // 延迟发送的消息.
        $delay = [];
        $now = time();

        foreach ($list as $serialized) {
            /**
             * @var ConversationMessage $unserialized
             */
            $unserialized = unserialize($serialized);
            if (!$unserialized instanceof ConversationMessage) {
                // 一般不会出现这种情况. 除非 conversationMessage 本身不能序列化
                continue;
            }

            // 发送时间.
            $deliverAt = $unserialized->message->getDeliverAt();

            if (!isset($deliverAt) || $deliverAt->timestamp < $now) {
                array_unshift($rendering, $unserialized);
            } else {
                $delay[] = $serialized;
            }
        }

        if (!empty($delay)) {
            $this->bufferToCache($delay);
        }
        return $rendering;
    }



}
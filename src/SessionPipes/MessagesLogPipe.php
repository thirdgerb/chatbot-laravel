<?php

/**
 * Class MessagesLogPipe
 * @package Commune\Chatbot\Laravel\SessionPipes
 */

namespace Commune\Chatbot\Laravel\SessionPipes;


use Commune\Chatbot\Blueprint\Conversation\ConversationMessage;
use Commune\Chatbot\OOHost\Session\Session;
use Commune\Chatbot\OOHost\Session\SessionPipe;
use Psr\Log\LoggerInterface;

class MessagesLogPipe implements SessionPipe
{
    public function handle(Session $session, \Closure $next): Session
    {
        $session = $next($session);
        /**
         * @var LoggerInterface $logger
         * @var Session $session
         */
        $conversation = $session->conversation;
        $logger = $conversation->make('log.messages');
        $name = $conversation->getUser()->getName();
        $sessionId = $session->sessionId;


        $incoming = $conversation->getIncomingMessage();
        $this->log($name, $logger, $incoming, $sessionId, true);

        $messages = $conversation->getConversationReplies();
        foreach ($messages as $message) {
            $this->log($name, $logger, $message, $sessionId, false);
        }

        return $session;
    }

    /**
     * @param string $name
     * @param LoggerInterface $logger
     * @param ConversationMessage $message
     * @param string $sessionId
     * @param bool $fromUser
     */
    protected function log(
        string $name,
        LoggerInterface $logger,
        ConversationMessage $message,
        string $sessionId,
        bool $fromUser
    ) : void
    {

        $from = $fromUser ? 'from_user' : 'to_user';
        $uid = $message->getUserId();
        $text = json_encode($message->getMessage()->getTrimmedText(), JSON_UNESCAPED_UNICODE);
        $trace = $message->getTraceId();
        $logger->info("$text $from:$name sid:$sessionId trace:$trace uid:$uid");
    }


}
<?php

/**
 * Class ExceptionHandlerImpl
 * @package Commune\Chatbot\Laravel\Impl
 */

namespace Commune\Chatbot\Laravel\Impl;


use Commune\Chatbot\Framework\Exceptions\ChatbotException;
use Commune\Chatbot\Framework\Message\Message;
use Commune\Chatbot\Framework\Message\Text;
use Commune\Chatbot\Contracts\ExceptionHandler;
use Psr\Log\LoggerInterface;

class ExceptionHandlerImpl implements ExceptionHandler
{
    /**
     * @var LoggerInterface
     */
    protected $log;

    /**
     * ExceptionHandlerImpl constructor.
     * @param LoggerInterface $log
     */
    public function __construct(LoggerInterface $log)
    {
        $this->log = $log;
    }


    public function handle(\Exception $e)
    {
        $this->log->error($e);
    }

    public function render(ChatbotException $e): Message
    {
        return new Text(strval($e));
    }

}
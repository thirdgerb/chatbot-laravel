<?php

/**
 * Class ConsoleDriver
 * @package Commune\Chatbot\Laravel\Impl
 */

namespace Commune\Chatbot\Laravel\Impl;


use Commune\Chatbot\Contracts\ServerDriver;
use Commune\Chatbot\Framework\Character\Platform;
use Commune\Chatbot\Framework\Character\Recipient;
use Commune\Chatbot\Framework\Character\User;
use Commune\Chatbot\Framework\Conversation\Conversation;
use Commune\Chatbot\Framework\Message\Message;
use Commune\Chatbot\Framework\Message\Text;
use Commune\Chatbot\Laravel\Console\ConsolePlatform;
use Commune\Chatbot\Laravel\Console\ConsoleTest;

class ConsoleDriver implements ServerDriver
{
    /**
     * @var ConsoleTest
     */
    protected $command;

    protected $userId;

    protected $userName;

    public function __construct(ConsoleTest $command, string $userId, string $userName)
    {
        $this->command = $command;
        $this->userId = $userId;
        $this->userName = $userName;

    }

    public function fetchSender($request): User
    {
        return new User(
            $this->userId,
            $this->userName,
            $this->userId,
            $this->getPlatform()
        );
    }

    public function fetchRecipient($request): Recipient
    {
        return new Recipient(
            'test',
            'laravel_console_test',
            'test',
            $this->getPlatform()
        );
    }

    public function fetchMessage($request): Message
    {
        return new Text($request);
    }

    public function reply(Conversation $conversation)
    {
        foreach ($conversation->getReplies() as $reply) {
            $this->sayMessage($reply);
        }
    }

    protected function sayMessage(Message $message)
    {
        if ($message instanceof Text) {

            $type = $message->getStyle();
            $verbosity = $message->getVerbosity();
            $text = $message->getText();
            $lines = explode("\n", $text);

            switch ($type) {
                case Text::WARN :
                    $style = 'warn';
                    break;
                case Text::ERROR :
                    $style = 'error';
                    break;
                case Text::INFO :
                default:
                    $style = 'info';
            }

            foreach ($lines as $line) {
                $this->command->line($line, $style, $verbosity);
            }

        } else {
            $lines = explode("\n", $message->getText());
            foreach ($lines as $line) {
                $this->command->info($line);
            }
        }

    }

    public function error(\Exception $e)
    {
        $this->command->error($e->getMessage());
    }

    public function getPlatform(): Platform
    {
        return new ConsolePlatform();
    }

    public function close()
    {
        $this->command->info('bye');
        exit;
    }


}
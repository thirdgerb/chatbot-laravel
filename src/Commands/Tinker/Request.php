<?php

/**
 * Class Request
 * @package Commune\Chatbot\Laravel\Commands\Tinker
 */

namespace Commune\Chatbot\Laravel\Commands\Tinker;



use Commune\Chatbot\App\Messages\Text;
use Commune\Chatbot\App\Platform\ConsoleConfig;
use Commune\Chatbot\Blueprint\Conversation\ConversationMessage;
use Commune\Chatbot\Blueprint\Message\Message;
use Commune\Chatbot\Blueprint\Message\VerboseMsg;
use Commune\Chatbot\Laravel\Drivers\LaravelMessageRequest;
use Illuminate\Console\Command;

class Request extends LaravelMessageRequest
{

    /**
     * @var Command
     */
    protected $command;

    /**
     * @var ConsoleConfig
     */
    protected $config;

    /**
     * Request constructor.
     * @param Command $command
     * @param string|Message $text
     * @param ConsoleConfig $config
     */
    public function __construct(Command $command, $text, ConsoleConfig $config)
    {
        $this->command = $command;
        $this->config = $config;
        parent::__construct($text);
    }

    public function getChatbotUserId(): string
    {
        return $this->config->chatbotUserId;
    }

    public function getPlatformId(): string
    {
        return Server::class;
    }

    protected function makeInputMessage(): Message
    {
        return new Text($this->input);
    }

    public function fetchUserId(): string
    {
        return $this->config->consoleUserId;
    }

    public function fetchUserName(): string
    {
        return $this->config->consoleUserName;
    }

    public function fetchUserData(): array
    {
        return [];
    }

    /**
     * @param ConversationMessage[] $buffer
     */
    public function renderChatMessages(array $buffer): void
    {
        foreach ($buffer as $message) {
            $msg = $message->getMessage();
            $text = $msg->getText().PHP_EOL;

            if ($msg instanceof VerboseMsg) {
                switch($msg->getLevel()) {
                    case VerboseMsg::ERROR :
                        $this->command->error($text);
                        break;
                    case VerboseMsg::WARN :
                        $this->command->warn($text);
                        break;
                    default :
                        $this->command->info($text);
                        break;
                }
            } else {
                $this->command->info($text);
            }
        }
    }

}
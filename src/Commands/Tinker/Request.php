<?php

/**
 * Class Request
 * @package Commune\Chatbot\Laravel\Commands\Tinker
 */

namespace Commune\Chatbot\Laravel\Commands\Tinker;



use Commune\Chatbot\App\Messages\Text;
use Commune\Chatbot\App\Platform\ConsoleConfig;
use Commune\Chatbot\Blueprint\Conversation\ConversationMessage;
use Commune\Chatbot\Blueprint\Conversation\MessageRequest;
use Commune\Chatbot\Blueprint\Message\Message;
use Commune\Chatbot\Blueprint\Message\VerboseMsg;
use Commune\Support\Uuid\HasIdGenerator;
use Commune\Support\Uuid\IdGeneratorHelper;
use Illuminate\Console\Command;

class Request implements MessageRequest, HasIdGenerator
{
    use IdGeneratorHelper;

    /**
     * @var Command
     */
    protected $command;

    /**
     * @var string|Message
     */
    protected $text;

    /**
     * @var ConsoleConfig
     */
    protected $config;

    /**
     * @var Message
     */
    protected $message;

    /**
     * @var string
     */
    protected $messageId;

    /**
     * @var ConversationMessage[]
     */
    protected $buffer = [];

    /**
     * Request constructor.
     * @param Command $command
     * @param string|Message $text
     * @param ConsoleConfig $config
     */
    public function __construct(Command $command, $text, ConsoleConfig $config)
    {
        $this->command = $command;
        $this->text = $text;
        $this->config = $config;
    }


    public function generateMessageId(): string
    {
        return $this->createUuId();
    }

    public function getChatbotUserId(): string
    {
        return $this->config->chatbotUserId;
    }

    public function getPlatformId(): string
    {
        return Server::class;
    }

    public function fetchMessage(): Message
    {
        if ($this->text instanceof Message) {
            return $this->text;
        }
        return $this->message
            ?? $this->message = new Text($this->text);

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

    public function bufferMessageToChat(ConversationMessage $message): void
    {
        $this->buffer[] = $message;
    }

    public function flushChatMessages(): void
    {
        foreach ($this->buffer as $message) {
            $msg = $message->getMessage();
            $text = $msg->getText();

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

    public function finishRequest(): void
    {
    }


}
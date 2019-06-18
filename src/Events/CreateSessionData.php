<?php

/**
 * Class CreateContext
 * @package Commune\Chatbot\Laravel\Events
 * @author BrightRed
 */

namespace Commune\Chatbot\Laravel\Events;

use Commune\Chatbot\OOHost\Session\Session;
use Commune\Chatbot\OOHost\Session\SessionData;
use Symfony\Component\EventDispatcher\Event;

class CreateSessionData extends Event
{
    /**
     * @var SessionData
     */
    public $sessionData;

    /**
     * @var Session
     */
    public $session;

    /**
     * CreateSessionData constructor.
     * @param SessionData $sessionData
     * @param Session $session
     */
    public function __construct(SessionData $sessionData, Session $session)
    {
        $this->sessionData = $sessionData;
        $this->session = $session;
    }


}
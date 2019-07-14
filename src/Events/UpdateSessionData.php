<?php

/**
 * Class UpdateContext
 * @package Commune\Chatbot\Laravel\Events
 * @author BrightRed
 */

namespace Commune\Chatbot\Laravel\Events;


use Commune\Chatbot\OOHost\Session\Session;
use Commune\Chatbot\OOHost\Session\SessionData;
use Symfony\Component\EventDispatcher\Event;

class UpdateSessionData extends Event
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
     * UpdateSessionData constructor.
     * @param SessionData $sessionData
     * @param Session $session
     */
    public function __construct(SessionData $sessionData, Session $session)
    {
        $this->sessionData = $sessionData;
        $this->session = $session;
    }


}
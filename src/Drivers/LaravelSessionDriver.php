<?php

/**
 * Class SessionDriver
 * @package Commune\Chatbot\Laravel\Drivers
 * @author BrightRed
 */

namespace Commune\Chatbot\Laravel\Drivers;


use Carbon\Carbon;
use Commune\Chatbot\Blueprint\Conversation\ConversationLogger;
use Commune\Chatbot\Config\Host\OOHostConfig;
use Commune\Chatbot\Contracts\EventDispatcher;
use Commune\Chatbot\Framework\Conversation\RunningSpyTrait;
use Commune\Chatbot\Laravel\Database\TableSchema;
use Commune\Chatbot\Laravel\Events\CreateSessionData;
use Commune\Chatbot\Laravel\Events\UpdateSessionData;
use Commune\Chatbot\OOHost\Context\Context;
use Commune\Chatbot\OOHost\History\Breakpoint;
use Commune\Chatbot\OOHost\History\Yielding;
use Commune\Chatbot\OOHost\Session\Driver as SessionDriver;
use Commune\Chatbot\OOHost\Session\Session;
use Commune\Chatbot\OOHost\Session\SessionData;
use Illuminate\Database\ConnectionInterface;

class LaravelSessionDriver implements SessionDriver
{
    use RunningSpyTrait;

    /**
     * @var ConnectionInterface
     */
    protected $db;

    /**
     * @var OOHostConfig
     */
    protected $hostConfig;

    /**
     * @var ConversationLogger
     */
    protected $logger;

    /**
     * @var EventDispatcher
     */
    protected $dispatcher;

    /**
     * @var LaravelDBDriver
     */
    protected $driver;

    /**
     * @var string
     */
    protected $traceId;

    /**
     * LaravelSessionDriver constructor.
     * @param LaravelDBDriver $driver
     * @param OOHostConfig $hostConfig
     * @param ConversationLogger $logger
     * @param EventDispatcher $dispatcher
     */
    public function __construct(
        LaravelDBDriver $driver,
        OOHostConfig $hostConfig,
        ConversationLogger $logger,
        EventDispatcher $dispatcher
    )
    {
        $this->hostConfig = $hostConfig;
        $this->logger = $logger;
        $this->dispatcher = $dispatcher;
        $this->driver = $driver;
        $this->traceId = $driver->getTraceId();
        self::addRunningTrace($this->traceId, $this->traceId);
    }


    /**
     * @return \Illuminate\Database\Connection
     */
    protected function db()
    {
        return $this->driver->getDB();
    }

    /**
     * @return \Illuminate\Redis\Connections\Connection
     */
    protected function cache()
    {
        return $this->driver->getRedis();
    }

    protected function tableName(string $type) : string
    {
        switch ($type) {
            case SessionData::CONTEXT_TYPE :
                return TableSchema::CONTEXTS_TABLE;

            case SessionData::BREAK_POINT :
                return TableSchema::BREAKPOINTS_TABLE;

            default :
                return TableSchema::SESSION_DATA_TABLE;
        }
    }

    protected function dataIdField(string $type) : string
    {
        switch ($type) {
            case SessionData::CONTEXT_TYPE :
                return 'context_id';
            case SessionData::BREAK_POINT :
                return 'breakpoint_id';
            default :
                return 'session_data_id';
        }
    }

    protected function cacheKey(string $id, string $dataType) : string
    {
        switch ($dataType) {
            case SessionData::CONTEXT_TYPE :
                $type = 'context';
                break;
            case SessionData::BREAK_POINT :
                $type = 'breakpoint';
                break;
            default :
                $type = 'sessionData';
        }
        return "chatbot:host:$type:id:$id";
    }


    public function saveYielding(Session $session, Yielding $yielding): void
    {
        $this->saveSessionData($session, $yielding);
    }

    public function findYielding(string $contextId): ? Yielding
    {
        $data = $this->findSessionData(
            $contextId,
            SessionData::YIELDING_TYPE
        );

        return $data instanceof Yielding ? $data : null;
    }

    public function saveBreakpoint(Session $session, Breakpoint $breakpoint): void
    {
        $this->saveSessionData(
            $session,
            $breakpoint
        );
    }


    public function saveSessionData(
        Session $session,
        SessionData $sessionData
    ) : void
    {
        $id = $sessionData->getSessionDataId();
        $type = $sessionData->getSessionDataType();
        $cacheKey = $this->cacheKey($id, $type);
        $cache = $this->cache();

        $serialized = serialize($sessionData);

        $cache->setex(
            $cacheKey,
            $this->hostConfig->sessionExpireSeconds,
            $serialized
        );

        $table = $this->tableName($type);
        $idField = $this->dataIdField($type);
        $exists = $this->db()
            ->table($table)
            ->where($idField, '=', $id)
            ->exists();

        if (!$exists) {
            $data = TableSchema::getScopeFromSession($session);
            $data[$idField] = $id;
            $data['content'] = $serialized;

            if ($table === TableSchema::SESSION_DATA_TABLE) {
                $data['session_data_type'] = $type;
            }
            $data['created_at'] = $data['updated_at'] = new Carbon();

            $result = $this->db()
                ->table($table)
                ->insert($data);

            $this->dispatcher->dispatch(
                new CreateSessionData($sessionData, $session)
            );

        } else {
            $result = $this->db()
                ->table($table)
                ->where($idField, '=', $id)
                ->update([
                    'content' => $serialized,
                    'updated_at' => new Carbon()
                ]);

            $this->dispatcher->dispatch(new UpdateSessionData($sessionData, $session));
        }

        if ($result) {
            return;
        }

        $this->logger->error(
            __METHOD__
            . ' save or updated fail',
            [
                'exists' => $exists,
                'table' => $table,
                'id' => $id,
                'type' => $type
            ]
        );
    }

    public function findSessionData(string $id, string $dataType = '') : ? SessionData
    {
        $cacheKey = $this->cacheKey($id, $dataType);
        $cache = $this->cache();
        $value = $cache->get($cacheKey);

        if (!empty($value) && is_string($value)) {
            $object = unserialize($value);
            if ($object instanceof SessionData) {
                return $object;
            }

            $this->logger->error(
                __METHOD__
                . ' cached value is not valid. '
                . 'key is ' . $cacheKey . ', '
                . gettype($object) . ' given'
            );

        }

        $idField = $this->dataIdField($dataType);
        $table = $this->tableName($dataType);

        $data = $this->db()
            ->table($table)
            ->where($idField, $id)
            ->first('content');

        if (empty($data)) {
            return null;
        }

        $sessionData = unserialize($data->content);

        if ($sessionData instanceof SessionData) {
            $cache->setex(
                $cacheKey,
                $this->hostConfig->sessionExpireSeconds,
                $data->content
            );
            return $sessionData;
        }

        $this->logger->error(
            __METHOD__
            . ' saved value is not valid. '
            . 'key is ' . $cacheKey . ', '
            . gettype($sessionData) . ' given'
        );

        return null;
    }

    public function findBreakpoint(Session $session, string $id): ? Breakpoint
    {
        $data = $this->findSessionData($id, SessionData::BREAK_POINT);
        return $data instanceof Breakpoint ? $data : null;
    }


    public function saveContext(Session $session, Context $context): void
    {
        $this->saveSessionData($session, $context);
    }

    public function findContext(Session $session, string $contextId): ? Context
    {
        $data = $this->findSessionData($contextId, SessionData::CONTEXT_TYPE);
        return $data instanceof Context ? $data : null;
    }


    public function __destruct()
    {
        self::removeRunningTrace($this->traceId);
    }

}
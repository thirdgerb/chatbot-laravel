<?php

/**
 * Class SessionDriver
 * @package Commune\Chatbot\Laravel\Drivers
 * @author BrightRed
 */

namespace Commune\Chatbot\Laravel\Drivers;


use Commune\Chatbot\Config\Host\OOHostConfig;
use Commune\Chatbot\Contracts\CacheAdapter;
use Commune\Chatbot\Contracts\EventDispatcher;
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
use Illuminate\Support\Facades\DB;
use Psr\Log\LoggerInterface;

class LaravelSessionDriver implements SessionDriver
{
    /**
     * @var ConnectionInterface
     */
    protected $db;

    /**
     * @var CacheAdapter
     */
    protected $cache;

    /**
     * @var OOHostConfig
     */
    protected $hostConfig;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @var EventDispatcher
     */
    protected $dispatcher;

    /**
     * LaravelSessionDriver constructor.
     * @param CacheAdapter $cache
     * @param OOHostConfig $hostConfig
     * @param LoggerInterface $logger
     * @param EventDispatcher $dispatcher
     */
    public function __construct(
        CacheAdapter $cache,
        OOHostConfig $hostConfig,
        LoggerInterface $logger,
        EventDispatcher $dispatcher
    )
    {
        $this->cache = $cache;
        $this->hostConfig = $hostConfig;
        $this->logger = $logger;
        $this->dispatcher = $dispatcher;
    }


    /**
     * @return ConnectionInterface
     */
    protected function db()
    {
        if (isset($this->db)) {
            return $this->db;
        }
        $this->db = DB::connection();
        return $this->db;
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
        $serialized = serialize($sessionData);
        $this->cache->set(
            $cacheKey,
            $serialized,
            $this->hostConfig->sessionExpireSeconds
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
                    'content' => $serialized
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
        $value = $this->cache->get($cacheKey);

        if (is_string($value)) {
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

        if ($sessionData instanceof Breakpoint) {
            $this->cache->set(
                $cacheKey,
                $data->content,
                $this->hostConfig->sessionExpireSeconds
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
        if (CHATBOT_DEBUG) {
            $this->logger->debug(__METHOD__);
        }
    }

}
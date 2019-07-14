<?php

namespace Commune\Chatbot\Laravel\SessionCommands;


use Commune\Chatbot\Blueprint\Message\Command\CmdMessage;
use Commune\Chatbot\Contracts\CacheAdapter;
use Commune\Chatbot\Laravel\Drivers\LaravelDBDriver;
use Commune\Chatbot\OOHost\Command\SessionCommand;
use Commune\Chatbot\OOHost\Command\SessionCommandPipe;
use Commune\Chatbot\OOHost\Session\Driver;
use Commune\Chatbot\OOHost\Session\Session;

class RunningSpyCmd extends SessionCommand
{
    const SIGNATURE = 'runningSpy
        {--a|all : 查看所有选项的数量}
        {--c|conversation : 检查conversation 的实例数量.}
        {--s|session : 检查session 的实例数量.}
        {--d|database : 检查 laravel database driver 的实例数量.}
        {--cacheAdapter : 检查 cacheAdapter 的实例数量.}
        {--sessionDriver : 检查 sessionDriver 的实例数量.}
';

    const DESCRIPTION = '查看一些关键类的实例数量. 用于排查部分内存泄露问题.';

    public function handle(CmdMessage $message, Session $session, SessionCommandPipe $pipe): void
    {

        $all = (bool) $message['--all'];
        $detail = ! $all;
        $running = $all;

        if ($all || $message['--conversation']) {
            $this->showRunningTrace(
                'conversation',
                $session->conversation->getRunningTraces(),
                $detail
            );
            $running = true;
        }

        if ($all || $message['--session']) {
            $this->showRunningTrace(
                'session',
                $session->getRunningTraces(),
                $detail
            );
            $running = true;
        }

        if ($all || $message['--database']) {
            $this->showRunningTrace(
                'laravel database',
                $session->conversation
                    ->make(LaravelDBDriver::class)
                    ->getRunningTraces(),
                $detail
            );
            $running = true;
        }

        if ($all || $message['--sessionDriver']) {
            $this->showRunningTrace(
                'sessionDriver',
                $session->conversation
                    ->make(Driver::class)
                    ->getRunningTraces(),
                $detail
            );
            $running = true;
        }


        if ($all || $message['--cacheAdapter']) {
            $this->showRunningTrace(
                'cacheAdapter',
                $session->conversation
                    ->make(CacheAdapter::class)
                    ->getRunningTraces(),
                $detail
            );
            $running = true;
        }


        if (!$running) {
            $this->say()->warning('需要配合参数使用. 请加上 -h 查看');
        }
    }

    protected function showRunningTrace(string $type, array $traces, bool $showDetail) : void
    {
        $c = count($traces);

        $slices = array_slice($traces, 0, 20);

        $output = "$type 运行中实例共 $c 个 \n";
        if ($showDetail) {
            $output .= "列举最多20个如下:\n";
            foreach ($slices as $trace => $id) {
                $output .= "  $trace : $id\n";
            }
        }

        $this->say()
            ->info($output);
    }

}
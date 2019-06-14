<?php
namespace PShelf\Ssr\Worker;
use PShelf\Ssr\Logger;
use Monolog\Logger as Monologger;
use Monolog\Handler\StreamHandler;
use React\EventLoop\Factory;
use React\Socket\Server as ReactServer;
use React\Socket\ConnectionInterface;


class Server {
  protected $logger;
  protected $server;
  protected $prerenderer;

  public function __construct() {
    $this->logger = new Logger('WorkerServer');
    $this->prerenderer = new Prerenderer();
    $this->process = new Process();
    $this->cleanup();
    $this->initServer();
  }

  private function cleanup() {
    $this->process->killChrome();
  }

  public function onData($data) {
    $payload = unserialize($data);
    $this->prerenderer->fetch($payload->id, $payload->url);
  }

  public function onClose() {
    $this->logger->info('Client disconnected');
  }

  public function onError($exception) {
    $this->logger->error($exception->getMessage());
  }

  public function onConnection($connection) {
    $addr = $connection->getRemoteAddress();
    $this->logger->info('Client connected from:', $addr);
    $connection->on('data', [$this, 'onData']);
    $connection->on('error', [$this, 'onError']);
    $connection->on('close', [$this, 'onClose']);
  }

  private function initServer() {
    $loop = Factory::create();
    // $loop->addPeriodicTimer(5, [$this, 'keepAlive']);
    $this->logger->info('Creating server');
    $this->server = new ReactServer(SSR_SERVER_BIND_ADDR, $loop);
    $this->logger->info('Server listening on:', $this->server->getAddress());
    $this->server->on('connection', [$this, 'onConnection']);
    $loop->run();
  }
}
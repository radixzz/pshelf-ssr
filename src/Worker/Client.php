<?php
namespace PShelf\Ssr\Worker;
use PShelf\Ssr\Logger;
use PShelf\Ssr\Worker\Process;
use React\EventLoop\Factory;
use React\Socket\Connector;
use React\Stream\WritableResourceStream;

class Client {
  protected $logger;
  protected $client;

  public function __construct() {
    $this->logger = new Logger('WorkerClient');
    $this->loop = Factory::create();
    $this->client = new Connector($this->loop);
  }

  public function push($view, $url) {
    $loop = $this->loop;
    $payload = (object) [
      'id' => $view->cacheKey,
      'url' => $url,
    ];
    $this->client->connect(SSR_SERVER_BIND_ADDR)->then(
      function($connection) use ($loop, $payload) {
        $stream = fopen('php://stdout', 'w');
        $connection->pipe(new WritableResourceStream($stream, $loop));
        $connection->write(serialize($payload));
        $connection->end();
      }
    );
    $loop->run();
  }
}

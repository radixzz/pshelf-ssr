<?php
namespace PShelf\Ssr\Command;
use PShelf\Ssr\Command\CommandKill;
use PShelf\Ssr\Command\CommandSpawn;
use PShelf\Ssr\Command\CommandStart;
use PShelf\Ssr\Command\CommandClearCache;
use Symfony\Component\Console\Application;

class Client {
  protected $app;

  public function __construct() {
    $this->app = new Application();
    $this->app->add(new CommandKill());
    $this->app->add(new CommandSpawn());
    $this->app->add(new CommandStart());
    $this->app->add(new CommandClearCache());
    $this->app->run();
  }
}

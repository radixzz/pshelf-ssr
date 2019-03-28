<?php
namespace PShelf\Ssr;
use Monolog\Logger as Monologger;
use Monolog\Handler\StreamHandler;

class Logger {
  protected $instance;
  protected $dumper;

  public function __construct($channel) {
    touch(SSR_LOG_FILE);
    $this->instance = new Monologger($channel);
    $this->instance->pushHandler(new StreamHandler(SSR_LOG_FILE, Monologger::DEBUG));
  }

  protected function isCmdLine() {
    return php_sapi_name() === 'cli';
  }

  protected function mergeEntries($arr) {
    $result = [];
    $idx = 0;
    foreach($arr as $item) {
      if (is_string($item)) {
        $result[$idx] = $result[$idx] ?? '';
        $result[$idx] .= $item . ' ';
      } else {
        $idx += 1;
        $result[] = $item;
      }
    }
    return $result;
  }

  public function dump($args) {
    if ($this->isCmdLine()) {
      dump($args);
    }
  }

  protected function handle($args) {
    $mergedArgs = $this->mergeEntries($args);
    $first = array_shift($mergedArgs);
    $this->dump($first, $mergedArgs);
    return [$first, $mergedArgs];
  }

  public function info() {
    if ($this->instance) {
      list($first, $all) = $this->handle(func_get_args());
      $this->instance->info($first, $all);
    }
  }

  public function error() {
    if ($this->instance) {
      list($first, $all) = $this->handle(func_get_args());
      $this->instance->error($first, $all);
    }
  }
}

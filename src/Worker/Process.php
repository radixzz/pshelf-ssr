<?php
namespace PShelf\Ssr\Worker;
use PShelf\Ssr\Logger;
use Symfony\Component\Process\Process as SymfonyProcess;
use Symfony\Component\Process\PhpExecutableFinder;
use Symfony\Component\Process\Exception\ProcessFailedException;

class Process {
  public function __construct() {
    $this->logger = new Logger('WorkerProcess');
  }

  public function getPhpPath() {
    $phpBinaryFinder = new PhpExecutableFinder();
    return $phpBinaryFinder->find();
  }

  public function processCommand($cmd, $background = false) {
    $proc = new SymfonyProcess($cmd);
    try {
      $proc->start();
      if (!$background) {
        if (0 !== $proc->wait()) {
          throw new ProcessFailedException($proc);
        }
      }
    } catch (ProcessFailedException $exception) {
      $this->logger->error($exception->getMessage());
    }
    return $proc;
  }

  public function spawn() {
    $script = SSR_CLI_SCRIPT;
    $phpPath = $this->getPhpPath();
    $cmd = "{$phpPath} {$script} ssr:start > /dev/null &";
    $proc = $this->processCommand($cmd, true);
    sleep(5);
    $this->logger->info('Process spawned');
  }

  public function getCmdOutput($cmd) {
    $out = '';
    $proc = $this->processCommand($cmd);
    $out = $proc->getOutput();
    return $out;
  }

  public function getRunningPids() {
    // get pids of running scripts
    $cmd = "ps -eo pid,command | grep ssr:start | grep -v grep | awk '{print $1}'";
    $output = $this->getCmdOutput($cmd);
    // split string output by new line
    $arr = preg_split('/\r\n|\r|\n/', $output);
    // filter empty lines
    return array_filter($arr, function($v) {
      return !empty($v);
    });
  }

  public function killChrome() {
    $this->processCommand('killall chrome -qw', true);
    sleep(3);
  }

  public function killAll() {
    $pids = $this->getRunningPids();
    $acmd = array_merge(['kill', '-9'], $pids);
    $this->processCommand($acmd);
  }

  public function running() {
    $pids = $this->getRunningPids();
    return count($pids) > 0;
  }

}
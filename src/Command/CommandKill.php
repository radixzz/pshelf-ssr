<?php
namespace PShelf\Ssr\Command;

use PShelf\Ssr\Worker\Process;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CommandKill extends Command {
  protected static $defaultName = 'ssr:kill';

  protected function configure() {
    $this->setDescription('Kill the active worker.');
    $this->setHelp('Kills any worker instance running in the system');
  }

  protected function execute(InputInterface $input, OutputInterface $output) {
    $proc = new Process();
    $pids = $proc->getRunningPids();
    $pids = array_filter($pids, function($pid) {
      return (int)$pid !== getmypid();
    });
    $pidsCount = count($pids);
    if ($pidsCount > 0) {
      $output->writeln("[${pidsCount}] workers killed.");
      $proc->killAll();
    } else {
      $output->writeln("No active worker found.");
    }
  }
}
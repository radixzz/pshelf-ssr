<?php
namespace PShelf\Ssr\Command;
use PShelf\Ssr\Worker\Process;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CommandSpawn extends Command {
  protected static $defaultName = 'ssr:spawn';

  protected function configure() {
    $this->setDescription('Spawns the worker server.');
    $this->setHelp('Spawns a new instance of a worker and starts listening to any prerender commands');
  }

  protected function execute(InputInterface $input, OutputInterface $output) {
    $output->writeln('Spawning process');
    $proc = new Process();
    $proc->spawn();
    $output->writeln('Done.');
  }
}
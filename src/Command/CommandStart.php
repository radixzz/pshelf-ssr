<?php
namespace PShelf\Ssr\Command;
use PShelf\Ssr\Worker\Server;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CommandStart extends Command {
  protected static $defaultName = 'ssr:start';

  protected function configure() {
    $this->setDescription('Start\'s a server instance.');
    $this->setHelp('Creates an instance of a server in the current process.');
  }

  protected function execute(InputInterface $input, OutputInterface $output) {
    $output->writeln('Starting server instance');
    $proc = new Server();
    $output->writeln('Server instance terminated.');
  }
}
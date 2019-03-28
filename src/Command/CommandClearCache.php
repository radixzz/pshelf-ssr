<?php
namespace PShelf\Ssr\Command;

use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Filesystem\Exception\IOExceptionInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CommandClearCache extends Command {
  protected static $defaultName = 'ssr:clear-cache';

  protected function configure() {
    $this->setDescription('Clears the ssr cache.');
    $this->setHelp('Deletes all cached files from the cache folder');
  }

  protected function execute(InputInterface $input, OutputInterface $output) {
    $output->writeln('Clearing cache');
    try {
      $fs = new Filesystem();
      $fs->remove(SSR_PRERENDER_PATH);
    } catch (IOExceptionInterface $exception) {
      $msg = $exception->getPath();
      $output->writeln('Operation failed while removing dir at: ' . $msg);
    }
  }
}
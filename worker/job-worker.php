<?php
use Phpfastcache\CacheManager;
use Phpfastcache\Drivers\Files\Config as FileConfig;
use Nesk\Rialto\Data\JsFunction;
use Nesk\Puphpeteer\Puppeteer;
use Monolog\Logger as Monologger;
use Monolog\Handler\StreamHandler;
use React\EventLoop\Factory;
use React\Socket\Server;
use React\Socket\ConnectionInterface;
use FooSSR\Logger;

class SSRJobWorker {
  protected $logger;
  protected $server;

  public function __construct($args) {
    register_shutdown_function(function() {
      dump('process killed');
    });
    $this->initLogger();
    $this->initServer();
    $this->start();
  }

  private function initServer() {
    $logger = $this->logger;
    $loop = Factory::create();
    $this->server = new Server('127.0.0.1:44344', $loop);
    $this->server->on('connection', function($connection) use ($logger) {
      dump('client connected');
      $connection->on('data', function($data) use ($connection, $logger) {
        $logger->info('receiving: ' . $data);
        //$payload = unserialize($data);
        sleep(12);
        dump($data);
      });
      $connection->on('close', function() {
        dump("client disconnected");
      });
    });
    $loop->run();
  }

  private function initLogger() {
    touch(SSR_LOG_FILE);
    $this->logger = new Monologger('Worker');
    $this->logger->pushHandler(new StreamHandler(SSR_LOG_FILE, Monologger::DEBUG));
  }

  private function getPrerender($url) {
    $result = 'EOF';
    $puppeteer = new Puppeteer;
    $browser = $puppeteer->launch();
    $page = $browser->newPage();
    $blacklist = implode(',', SSR_HEADLESS_REQUESTS_BLACKLIST);
    $page->setRequestInterception(true);
    $page->on('request', JsFunction::createWithParameters(['req'])->body("
        const blacklist = '${blacklist}'.split(',');
        if (blacklist.find(regex => req.url().match(regex) ) ) {
          return req.abort();
        }
        req.continue();
    "));

    $page->evaluateOnNewDocument(JsFunction::createWithBody("
      window.__SSR_IS_PRENDERER = true;
    "));
    $page->goto($url);
    $page->waitForFunction(JsFunction::createWithBody("
      return window.__SSR_SAFE_TO_PRERENDER === true;
    "));
    $result = $page->content();
    $browser->close();
    return $result;
  }

  // private function getJob() {
  //   $result = null;
  //   $queue = $this->cache->getItemsByTag(SSR_CACHE_QUEUE_KEY);
  //   $this->logger->info('Getting Job');
  //   foreach ($queue as $key => $entry) {
  //     $job = $entry->get();
  //     if (!$job->running && !$job->done) {
  //       $job->running = true;
  //       $entry->set($job);
  //       $this->cache->save($entry);
  //       $this->logger->info('Job pop');
  //       return $job;
  //     }
  //   }
  //   return $result;
  // }

  // private function getRunningJobs() {
  //   $queue = $this->cache->getItemsByTag(SSR_CACHE_QUEUE_KEY);
  //   return count(array_filter($queue, function($entry) {
  //     $job = $entry->get();
  //     return $job->running && !$job->done;
  //   }));
  // }

  // private function isBusy() {
  //   $running = $this->getRunningJobs();
  //   return $running >= SSR_MAX_CONCURRENT_WORKERS;
  // }

  // private function cleanupJob($job, $error) {
  //   $entry = $this->cache->getItem($job->id);
  //   $updatedJob = $entry->get();
  //   $updatedJob->contents = $job->contents;
  //   $updatedJob->error = $error;
  //   $updatedJob->done = true;
  //   $updatedJob->running = false;
  //   $entry->set($updatedJob);
  //   $this->cache->save($entry);
  // }

  private function start() {
    while (true) {
      $this->logger->info('tick:' . getmypid() );
      usleep(2000);
    }

  //   if ($this->isBusy()) {
  //     $this->logger->info('Queue is busy');
  //     speep(1);
  //     $this->processQueue();
  //     return;
  //   }
  //   $job = $this->getJob();
  //   $error = '';
  //   if ($job) {
  //     try {
  //       $this->logger->info('Processing Job', [$job->url, $job->id]);
  //       $contents = $this->getPrerender($job->url);
  //       $targetPath = SSR_PRERENDER_PATH . "/{$job->id}.html";
  //       file_put_contents($targetPath, $contents);
  //       $job->contents = $contents;
  //     } catch (Exception $ex) {
  //       $error = $ex->getMessage();
  //       $this->logger->error('Job Error:', [$job->url, $job->id, $error]);
  //     } finally {
  //       $this->cleanupJob($job, $error);
  //       $this->logger->info('Job Finished', [$job->url, $job->id]);
  //       $this->processQueue();
  //     }
  //   } else {
  //     $this->logger->info('No more jobs to process');
  //   }
  // }
  }
}
new SSRJobWorker([]);
?>
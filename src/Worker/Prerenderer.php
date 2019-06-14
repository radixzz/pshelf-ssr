<?php
namespace PShelf\Ssr\Worker;

use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Filesystem\Exception\IOExceptionInterface;
use Nesk\Rialto\Exceptions\IdleTimeoutException;
use Nesk\Rialto\Exceptions\Node;
use PShelf\Ssr\Logger;
use Nesk\Rialto\Data\JsFunction;
use Nesk\Puphpeteer\Puppeteer;

class Prerenderer {

  protected $puppeteer;
  protected $browser;

  public function __construct() {
    $this->logger = new Logger('WorkerPrerenderer');
    $this->browserLogger = new Logger('Browser Log');
    $this->process = new Process();
    $this->fileSystem = new Filesystem();
  }

  protected function initPuppeteer() {
    if (empty($this->puppeteer)) {
      $this->logger->info("Puppeteer Initted");
      $this->puppeteer = new Puppeteer([
        'idle_timeout' => 360,
        'stop_timeout' => 10,
        'read_timeout' => 30,
        // 'logger' => $this->browserLogger->getInstance(),
        'log_node_console' => false,
        'log_browser_console' => false,
      ]);
      $this->browser = $this->puppeteer->launch();
    }
  }

  protected function releasePuppeteer() {
    unset($this->browser);
    unset($this->puppeteer);
  }

  protected function setLock($id, $active) {
    $lockFile = SSR_PRERENDER_PATH . "/${id}.lock";
    if ($active) {
      $this->fileSystem->touch($lockFile);
    } else {
      $this->fileSystem->remove($lockFile);
    }
  }

  public function fetch($id, $url) {
    try {
      $lockFile = SSR_PRERENDER_PATH . "/${id}.lock";
      $file = SSR_PRERENDER_PATH . "/${id}.html";
      if ($this->fileSystem->exists($lockFile) || $this->fileSystem->exists($file)) {
        $this->logger->info("Aborting fetch. Already queued:", $url);
        return;
      }
      $this->fileSystem->mkdir(SSR_PRERENDER_PATH);
      $this->setLock($id, true);
      $content = $this->getPageContent($url, $id);
      if (!empty($content)) {
        $this->fileSystem->dumpFile($file, $content);
      }
      $this->setLock($id, false);
    } catch (IOExceptionInterface $exception) {
      $this->logger->error($exception->getMessage());
    }
  }

  private function handleError($id, $e) {
    $this->logger->info($e->getMessage());
    $this->setLock($id, false);
  }

  public function getPageContent($url, $id) {
    $result = "";
    $this->initPuppeteer();
    $this->logger->info("Fetching URL:", $url);
    $page = $this->browser->newPage();
    try {
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
        document.body.classList.add('ssr-render');
      "));
      $page->goto($url);
      $page->waitForFunction(JsFunction::createWithBody("
        return window.__SSR_SAFE_TO_PRERENDER === true;
      "));
      $result = $page->content();
      $this->logger->info("Closing page");
      $page->close();
    } catch (Node\Exception $e) {
      $this->handleError($id, $e);
    } catch (IdleTimeoutException $e) {
      $this->handleError($id, $e);
    } catch (\Throwable $e) {
      $this->handleError($id, $e);
    } catch (\Exception $e) {
      $this->handleError($id, $e);
    }

    return $result;
  }
}

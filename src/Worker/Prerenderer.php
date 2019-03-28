<?php
namespace PShelf\Ssr\Worker;

use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Filesystem\Exception\IOExceptionInterface;
use Nesk\Rialto\Exceptions\IdleTimeoutException;
use PShelf\Ssr\Logger;
use Nesk\Rialto\Data\JsFunction;
use Nesk\Puphpeteer\Puppeteer;

class Prerenderer {

  protected $puppeteer;
  protected $browser;

  public function __construct() {
    $this->logger = new Logger('WorkerPrerenderer');
    $this->fileSystem = new Filesystem();
  }

  protected function initPuppeteer() {
    if (empty($this->puppeteer)) {
      dump('Prerender Init');
      $this->puppeteer = new Puppeteer([
        'idle_timeout' => 30
      ]);
      $this->browser = $this->puppeteer->launch();
    }
  }

  protected function releasePuppeteer() {
    unset($this->browser);
    unset($this->puppeteer);
  }

  public function fetch($id, $url) {
    try {
      $this->fileSystem->mkdir(SSR_PRERENDER_PATH);
      $content = $this->getPageContent($url);
      $file = SSR_PRERENDER_PATH . "/${id}.html";
      $this->fileSystem->dumpFile($file, $content);
    } catch (IOExceptionInterface $exception) {
      $this->logger->error($exception->getMessage());
    }

  }

  public function getPageContent($url) {
    $result = "";
    $this->initPuppeteer();
    try {
      $this->logger->info("Fetching URL:", $url);
      $page = $this->browser->newPage();
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
      $page->close();
      return $result;
    } catch (IdleTimeoutException $e)  {
      $this->logger->info("Waking up prerenderer");
      $this->releasePuppeteer();
      return $this->getPageContent($url);
    }
  }
}

<?php
namespace PShelf\Ssr;
use PShelf\Ssr\Worker\Client;
use PShelf\Ssr\Worker\Process;
use League\Uri\Components\Query;
use Symfony\Component\HttpFoundation\Response;

class Renderer {
  protected $logger;
  protected $request;
  protected $url;
  protected $client;

  public function __construct($args) {
    $this->defaultContent = $args['defaultContentsFile'];
    $this->passthrough = $args['passthrough'];
    $this->targetHost = $args['targetHost'];
    $this->logger = new Logger('Renderer');
    $this->request = new Request();
    $this->client = new Client();
    $this->process = new Process();
  }

  function render() {
    $req = $this->request;
    if ($req->isForbidden()) {
      $this->sendNotFoundResponse();
    } else if ($req->isSsrRequest() || $this->passthrough) {
      $this->sendDefaultResponse();
    } else {
      $this->sendCachedResponse();
    }
  }

  function sendCachedResponse() {
    $url = $this->request->getBaseUrl();
    $view = new CachedView($url);
    if (!$view->loaded) {
      $this->queueView($view);
      $view->waitForFile();
      if (!$view->loaded) {
        $this->sendDefaultResponse();
        return;
      }
    }
    $this->renderResponse($view->content, Response::HTTP_OK);
  }

  function queueView($view) {
    if (!$this->process->running()) {
      $this->process->spawn();
    }
    $ssrUrl = $this->request->getSsrUrl($this->targetHost);
    $this->client->push($view, $ssrUrl);
  }

  function sendDefaultResponse() {
    $content = file_get_contents($this->defaultContent);
    $this->renderResponse($content, Response::HTTP_OK);
  }

  function sendNotFoundResponse() {
    // Should be the 404 contents of vuejs
    $this->renderResponse('Not Found', Response::HTTP_NOT_FOUND);
  }

  function renderResponse($content, $statusCode) {
    $response = new Response();
    $response->setContent($content);
    $response->setStatusCode($statusCode);
    //$response->prepare($this->request);
    $response->send();
    exit();
  }
}

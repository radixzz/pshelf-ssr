<?php
namespace PShelf\Ssr;
use Symfony\Component\HttpFoundation\Request as SymfonyRequest;
use League\Uri\Components\Query;

class Request {

  public function __construct() {
    $this->req = SymfonyRequest::createFromGlobals();
  }

  public function isSsrRequest() {
    return $this->req->query->has(SSR_QUERY_PARAM_NAME);
  }

  public function getBaseUrl() {
    return $this->req->getSchemeAndHttpHost() . $this->req->getBaseUrl() . $this->req->getPathInfo();
  }

  public function getSsrUrl($ssrHost) {
    $query = new Query($this->req->getQueryString());
    // Add reserved query param to URL
    $query = $query->append(SSR_QUERY_PARAM_NAME . '=true');
    return $ssrHost . $this->req->getPathInfo() . '?' . $query;
  }

  public function getUrl() {
    $query = new Query($this->req->getQueryString());
    // Remove reserved query param from URL
    $query = $query->withoutParams([SSR_QUERY_PARAM_NAME]);
    $queryStr = '';
    if (count($query) > 0) {
      $queryStr = '?' . $query;
    }
    return $this->getBaseUrl() . $queryStr;
  }

  public function isForbidden() {
    $url = $this->getBaseUrl();
    if ($this->inList($url, SSR_WHITELIST)) {
      return false;
    } else {
      return $this->inList($url, SSR_BLACKLIST);
    }
  }

  // Taken from https://github.com/padosoft/support/blob/master/src/string.php
  private function strMatches($pattern, $value) {
      if ($pattern == $value) {
          return true;
      }
      $pattern = preg_quote($pattern, '#');
      $pattern = str_replace('\*', '.*', $pattern) . '\z';
      return preg_match('#^' . $pattern . '#', $value) === 1;
  }

  private function inList($str, $list) {
    foreach ($list as $pattern) {
      if ($this->strMatches($pattern, $str)) {
        return true;
      }
    }
    return false;
  }
}

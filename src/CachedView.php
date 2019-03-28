<?php
namespace PShelf\Ssr;

class CachedView {
  protected $url;
  protected $cacheKey;
  protected $content;
  protected $loaded = false;

  public function __construct($url) {
    $this->url = $url;
    $this->cacheKey = hash('crc32b', $url, false);
    $this->content = '';
    $this->restore();
  }

  public function restore() {
    if (!$this->loaded) {
      $this->restoreFromFile();
    }
  }

  private function restoreFromFile() {
    $fileContent = $this->getPrerenderedContent();
    if (!empty($fileContent)) {
      $this->content = $fileContent;
      $this->loaded = true;
    }
  }

  private function getPrerenderedContent() {
    $content = '';
    $id = $this->cacheKey;
    $file = SSR_PRERENDER_PATH . "/{$id}.html";
    if (file_exists($file)) {
      $content = file_get_contents($file);
    }
    return $content;
  }

  public function __get($property) {
    if (property_exists($this, $property)) {
      return $this->$property;
    }
  }
}

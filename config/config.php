<?php
call_user_func(function() {
  $base = dirname(__FILE__, 2);
  $config = [
    'SSR_LOG_FILE' => $base . '/ssr.log',
    'SSR_CACHE_PATH' => $base . '/cache',
    'SSR_CACHE_KEY' => 'ssr-prerender-cache',
    'SSR_CACHE_QUEUE_KEY' => 'ssr-queue',
    'SSR_PRERENDER_PATH' => $base . '/prerenders',
    'SSR_CLI_SCRIPT' => $base . '/cli.php',
    'SSR_QUERY_PARAM_NAME' => 'ssr_req',
    'SSR_SERVER_BIND_ADDR' => '127.0.0.1:44344',
    /*
      List of patterns to block when a request is made inside the
      headless instance. When a request is made inside the instance
      not all scripts need to be executed, specially tracking scripts.
    */
    'SSR_HEADLESS_REQUESTS_BLACKLIST' => [
      'urldefense.proofpoint.com',
      'fonts.googleapis.com',
      'maps.googleapis',
      'connect.facebook.net',
      'tt.mbww.com',
      'www.facebook.com',
      'analytics.js',
      'ga.js',
    ],
    /*
      List of globs to evaluate when a request is made to the SSR.
      By default we skip all the assets (those that dont need to be prerendered)
    */
    'SSR_BLACKLIST' => [
      '/front/*',
      '*.webmanifest',
      '*.php',
      '*.map',
      '*.js',
      '*.css',
      '*.xml',
      '*.jsp',
      '*.less',
      '*.png',
      '*.jpg',
      '*.jpeg',
      '*.svg',
      '*.gif',
      '*.json',
      '*.pdf',
      '*.doc',
      '*.txt',
      '*.ico',
      '*.rss',
      '*.zip',
      '*.mp3',
      '*.rar',
      '*.exe',
      '*.wmv',
      '*.doc',
      '*.avi',
      '*.ppt',
      '*.mpg',
      '*.mpeg',
      '*.tif',
      '*.wav',
      '*.mov',
      '*.psd',
      '*.ai',
      '*.xls',
      '*.mp4',
      '*.m4a',
      '*.swf',
      '*.dat',
      '*.dmg',
      '*.iso',
      '*.flv',
      '*.m4v',
      '*.torrent',
      '*.eot',
      '*.ttf',
      '*.otf',
      '*.woff',
      '*.woff2'
    ],
    'SSR_WHITELIST' => [
    ]
  ];
  foreach ($config as $key => $value) {
    // do not overwrite
    if (!defined($key)) {
      define($key, $value);
    }
  }
});

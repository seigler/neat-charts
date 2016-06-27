<?PHP
  // Adapted for The Art of Web: www.the-art-of-web.com
  // Based on PHP code by Dennis Pallett: www.phpit.net
  // Please acknowledge use of this code by including this header.

  // Updated to include and use last-modified and etag headers

  // location and prefix for cache files
  define('CACHE_PATH', "/tmp/cache_");

  // how long to keep the cache files (seconds)
  define('CACHE_TIME', 5 * 60);

  // return location and name for cache file
  function cache_file()
  {
    return CACHE_PATH . md5('buffer'.$_SERVER['REQUEST_URI']);
  }

  // display cached file if present and not expired
  function cache_display()
  {
    $file = cache_file();

    // check that cache file exists and is not too old
    if(!file_exists($file)) return;
    $last_modified_time = filemtime($file);
    if($last_modified_time < time() - CACHE_TIME) return;
    $etag = md5_file($file);

    // always send headers
    header("Last-Modified: ".gmdate("D, d M Y H:i:s", $last_modified_time)." GMT");
    header("Etag: $etag");
    header("Expires: ".gmdate("D, d M Y H:i:s", $last_modified_time + CACHE_TIME)." GMT");

    // tell any caches that I really mean it with these other headers
    header("Cache-Control: max-age=".CACHE_TIME.", must-revalidate");

    // exit if not modified
    if (@strtotime($_SERVER['HTTP_IF_MODIFIED_SINCE']) == $last_modified_time ||
        @trim($_SERVER['HTTP_IF_NONE_MATCH']) == $etag) {
        header("HTTP/1.1 304 Not Modified");
        exit;
    }

    // if so, display cache file and stop processing
    readfile($file);
    exit;
  }

  // write to cache file
  function cache_page($content)
  {
    if(false !== ($f = @fopen(cache_file(), 'w'))) {
      fwrite($f, $content);
      fclose($f);
    }
    $last_modified_time = time();
    $etag = md5_file('buffer'.$file);

    // always send headers
    header("Last-Modified: ".gmdate("D, d M Y H:i:s", $last_modified_time)." GMT");
    header("Etag: $etag");
    header("Expires: ".gmdate("D, d M Y H:i:s", $last_modified_time + CACHE_TIME)." GMT");

    return $content;
  }

  // execution stops here if valid cache file found
  cache_display();

  // enable output buffering and create cache file
  ob_start('cache_page');
?>

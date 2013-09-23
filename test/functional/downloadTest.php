<?php

$app = 'frontend';
if (!include(dirname(__FILE__).'/../bootstrap/functional.php'))
{
  return;
}

$b = new sfTestBrowser();

$b->get('/download/data')
  ->with('response')->begin()
    ->isStatusCode(200)
    ->isHeader('Pragma', 'cache')
    ->isHeader('Content-Disposition', 'inline; filename="foobar.txt"')
    ->isHeader('Content-Type', 'text/plain; charset=utf-8')
  ->end();

$b->get('/download/file')
  ->with('response')->begin()
    ->isStatusCode(200)
    ->isHeader('Pragma', 'no-cache')
    ->isHeader('Cache-Control', 'public')
    ->isHeader('Content-Disposition', 'attachment; filename="foo.pdf"')
    ->isHeader('Content-Type', 'application/pdf')
  ->end();

$since = filemtime(dirname(__FILE__) . '/fixtures/project/data/email/files/foo.pdf');

$b->setHttpHeader('If-Modified-Since', sfWebResponse::getDate($since))
  ->get('/download/fileCached')
  ->with('response')->begin()
    ->isStatusCode(304)
  ->end();

// FIXME: this implemented in httpdownload
$fst = stat(dirname(__FILE__) . '/fixtures/project/data/email/files/foo.pdf');
$md5 = md5($fst['mtime'] . '=' . $fst['size']);
$etag = '"' . $md5 . '-' . crc32($md5) . '"';

$b->setHttpHeader('If-None-Match', $etag)
  ->get('/download/fileCached')
  ->with('response')->begin()
    ->isStatusCode(304)
  ->end();

// with range
$b->setHttpHeader('Range', 'bytes=0-999')
  ->get('/download/fileCached')
  ->with('response')->begin()
    ->isStatusCode(206)
    // ->isHeader('Etag', $etag)
    ->isHeader('Content-Length', 1000)
    ->isHeader('Content-Disposition', 'attachment; filename="foo.pdf"')
    ->isHeader('Content-Type', 'application/pdf')
  ->end();

// invalid range!
$b->setHttpHeader('Range', 'bytes=5000-999')
  ->get('/download/fileCached')
  ->with('response')->begin()
    ->isStatusCode(416)
  ->end();

// invalid range!
$b->setHttpHeader('Range', 'I-WANT_TO_HACK-THIS-SITE')
  ->get('/download/fileCached')
  ->with('response')->begin()
    ->isStatusCode(416)
  ->end();

// custom etag
$b->get('/download/etag')
  ->with('response')->begin()
    ->isHeader('Etag', 'THIS-IS-AN-ETAG')
  ->end();

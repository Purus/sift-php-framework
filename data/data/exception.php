<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
  <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
  <title>Exception</title>
  <style type="text/css">
  body { margin: 0; padding: 2em; margin-top: 2em; background-color: #F3F2F1; }
  body, td, th { font: 9pt Verdana, Arial, sans-serif; color: #333 }
  a { color: #333 }
  h1 { margin: 1em 0 0 10px; padding: 10px 0 10px 0; font-weight: bold; font-size: 120% }
  h2 { margin: 0; padding: 5px 0; font-size: 110% }
  ul { padding: 0; padding-left: 20px; list-style: decimal; overflow: auto;  }
  ul li { padding-bottom: 5px; margin: 0; }
  ol { font-family: monospace; white-space: pre; list-style-position: inside; margin: 0; padding: 10px 0 }
  ol li { margin: -5px; padding: 0 }
  ol .selected { font-weight: bold; background-color: #ddd; padding: 2px 0 }
  table.vars { padding: 0; margin: 0; border: 1px solid #999; background-color: #fff; }
  table.vars th { padding: 2px; background-color: #ddd; font-weight: bold }
  table.vars td  { padding: 2px; font-family: monospace; white-space: pre }
  p.error { padding: 10px; background-color: #f00; font-weight: bold; text-align: center; -moz-border-radius: 10px; }
  p.error a { color: #fff }
  #main { 
    margin: 0 auto;
    padding: 2em;
    background-color: #fff;
    text-align:left;
    min-width: 20em;
    max-width: 60em;
    border: 1px solid #ccc;
    -moz-border-radius: 11px;
    -khtml-border-radius: 11px;
    -webkit-border-radius: 11px;
    border-radius: 5px;
    background: #fff;
    border: 1px solid #e5e5e5;
    -moz-box-shadow: rgba(200,200,200,1) 0 4px 18px;
    -webkit-box-shadow: rgba(200,200,200,1) 0 4px 18px;
    -khtml-box-shadow: rgba(200,200,200,1) 0 4px 18px;
    box-shadow: rgba(200,200,200,1) 0 4px 18px;
  }
  #message { overflow: auto; padding: 10px; margin-bottom: 10px; background-color: #A62400; color: #fff; -moz-border-radius: 3px; -webkit-border-radius: 3px; }
  </style>
  <script type="text/javascript">
  function toggle(id)
  {
    el = document.getElementById(id); el.style.display = el.style.display == 'none' ? 'block' : 'none';
  }
  </script>
</head>
<body>
  <div id="main">
  <h1>[<?php echo $name ?>]</h1>
  <h2 id="message"><?php echo $message ?></h2>
  <h2>stack trace</h2>
  <ul><li><?php echo implode('</li><li>', $traces) ?></li></ul>

  <h2>output <a href="#" onclick="toggle('sf_output'); return false;">...</a></h2>
  <div id="sf_output" style="display: none"><pre><?php echo $catchedOutput; ?></pre></div>

  <h2>symfony settings <a href="#" onclick="toggle('sf_settings'); return false;">...</a></h2>
  <div id="sf_settings" style="display: none"><?php echo $settingsTable ?></div>

  <h2>request <a href="#" onclick="toggle('sf_request'); return false;">...</a></h2>
  <div id="sf_request" style="display: none"><?php echo $requestTable ?></div>

  <h2>response <a href="#" onclick="toggle('sf_response'); return false;">...</a></h2>
  <div id="sf_response" style="display: none"><?php echo $responseTable ?></div>

  <h2>global vars <a href="#" onclick="toggle('sf_globals'); return false;">...</a></h2>
  <div id="sf_globals" style="display: none"><?php echo $globalsTable ?></div>

  <p id="footer">
    Sift <?php echo file_get_contents(sfConfig::get('sf_sift_lib_dir').'/VERSION') ?> - php <?php echo PHP_VERSION ?><br />
  </p>
  
  </div>
</body>
</html>

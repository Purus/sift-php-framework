<!DOCTYPE html>
<!--[if lt IE 7]>      <html class="no-js lt-ie9 lt-ie8 lt-ie7"> <![endif]-->
<!--[if IE 7]>         <html class="no-js lt-ie9 lt-ie8"> <![endif]-->
<!--[if IE 8]>         <html class="no-js lt-ie9"> <![endif]-->
<!--[if gt IE 8]><!--> <html class="no-js"> <!--<![endif]-->
  <head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <meta name="robots" content="noindex, noarchive">
    <title><?php echo $name; ?></title>
    <style type="text/css">

      body { 
        padding: 0;
        margin: 0; 
        background-color: #fff; 
      }

      body {
        font: 9pt/1.5 Verdana,sans-serif;color:#333;position:absolute;left:0;top:0;width:100%;z-index:23178;text-align:left
      }

      a { color: #b14300 }
      h1 { font-weight: normal; margin: 1em 0 0 10px; padding: 10px 0 10px 0; font-size: 18pt; }
      h2 { margin: 0; padding: 5px 0; font-size: 16pt; font-weight: normal; }
      ul { padding: 0; padding-left: 20px; list-style: decimal; overflow: auto;  }
      ul li { padding-bottom: 5px; margin: 0; }
      ol { font-family: monospace; white-space: pre; list-style-position: inside; margin: 0; padding: 10px 0 }
      ol li { margin: -5px; padding: 0 }
      ol .selected { font-weight: bold; background-color: #fff; padding: 2px 0 }
      table.vars { padding: 0; margin: 0; border: 1px solid #999; background-color: #fff; }
      table.vars th { padding: 2px; background-color: #ddd; font-weight: bold }
      table.vars td  { padding: 2px; font-family: monospace; white-space: pre }
      p.error { padding: 10px; background-color: #f00; font-weight: bold; text-align: center; -moz-border-radius: 10px; }
      p.error a { color: #fff }
      #main { 
        padding: 1em;
        background-color: #fff;
        text-align:left;
        background: #fff;
        max-width: 1200px;
        margin: 0 auto;
      }
      #message { 
        overflow: auto; 
        padding: 1em; 
        font-size: 12pt;
        margin-bottom: 10px; 
        background-color: #A62400; 
        font-weight: normal;
        color: #fff; 
        -moz-border-radius: 5px; 
        -webkit-border-radius: 5px; 
        border-radius: 5px;
        -moz-box-shadow: inset 0 0 5px #888;
        -webkit-box-shadow: inset 0 0 5px#888;
        box-shadow: inner 0 0 5px #888;
        background: #ffa84c; /* Old browsers */
        background: -moz-linear-gradient(top,  #ffa84c 0%, #ff7b0d 100%); /* FF3.6+ */
        background: -webkit-gradient(linear, left top, left bottom, color-stop(0%,#ffa84c), color-stop(100%,#ff7b0d)); /* Chrome,Safari4+ */
        background: -webkit-linear-gradient(top,  #ffa84c 0%,#ff7b0d 100%); /* Chrome10+,Safari5.1+ */
        background: -o-linear-gradient(top,  #ffa84c 0%,#ff7b0d 100%); /* Opera 11.10+ */
        background: -ms-linear-gradient(top,  #ffa84c 0%,#ff7b0d 100%); /* IE10+ */
        background: linear-gradient(to bottom,  #ffa84c 0%,#ff7b0d 100%); /* W3C */
        filter: progid:DXImageTransform.Microsoft.gradient( startColorstr='#ffa84c', endColorstr='#ff7b0d',GradientType=0 ); /* IE6-9 */


      }
      #message a {    
        color: #000;
      }

      .trace {
        background: #fbffb5;
        -moz-border-radius: 5px;
        -khtml-border-radius: 5px;
        -webkit-border-radius: 50px;
        border-radius: 5px;    
      }

      div#footer {
        margin: 1em 0;
        padding: 1em;
        background: #BBB;
        color: #000;
      }

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
      <h1><?php echo $name ?></h1>

      <h2 id="message"><?php echo $message ?></h2>

      <h2>Stack trace</h2>
      <ul><li><?php echo implode('</li><li>', $traces) ?></li></ul>

      <h2>Output <a href="#" onclick="toggle('sf_output'); return false;">...</a></h2>
      <div id="sf_output" style="display: none"><pre><?php echo $catchedOutput; ?></pre></div>

      <h2>Sift settings <a href="#" onclick="toggle('sf_settings'); return false;">...</a></h2>
      <div id="sf_settings" style="display: none"><?php echo $settingsTable ?></div>

      <h2>Request <a href="#" onclick="toggle('sf_request'); return false;">...</a></h2>
      <div id="sf_request" style="display: none"><?php echo $requestTable ?></div>

      <h2>Response <a href="#" onclick="toggle('sf_response'); return false;">...</a></h2>
      <div id="sf_response" style="display: none"><?php echo $responseTable ?></div>

      <h2>Global vars <a href="#" onclick="toggle('sf_globals'); return false;">...</a></h2>
      <div id="sf_globals" style="display: none"><?php echo $globalsTable ?></div>

      <div id="footer">
        Sift <?php sfCore::getVersion(); ?> - php <?php echo PHP_VERSION ?><br />
      </div>

    </div>

  </body>
</html>
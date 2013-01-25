<?php use_helper('Mail'); ?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
  <meta http-equiv="content-type" content="text/html; charset=utf-8" />  
  <style type="text/css">

      html { border: none; }

      body {
	      color: black;
	      padding: 20px 0;
	      background-color: #ccc;
      }
      
      table { border-collapse: collapse; font-family: "Trebuchet MS", Arial, Hevetica, sans-serif; border-color: #dddddd; }
      table td { font-size: 11pt; line-height: 13pt; vertical-align: middle; }
      table th { background: #cccccc; border: 1px solid #cccccc; font-size: 12pt; line-height: 13pt; font-family: Verdana, Arial, Hevetica, sans-serif; vertical-align: top; }

      #container { background-color: white; margin: 0 auto; }

      h1, h2, h3, h4, h5, h6 {
        font-family: "Trebuchet MS", Arial, Hevetica, sans-serif;
        font-weight: normal;
        margin-bottom: 10px;
      }

      h1, h2 { font-size: 1.6em; }
      h3, h4 { font-size: 1.2em; }

      h3 { font-weight: bold; color: #222; margin-bottom: 0.5em;  }
      h4 { font-weight: bold; margin-bottom: 5px; }

      p { margin-bottom: 1em; line-height: 150%; }
      .text-right { text-align: right; }

      p.footer { 
        margin-top: 11pt;
        font-size: 9pt;
      }

      td.top { vertical-align: top; }
      
      a:link, a:visited, a:hover { text-decoration: underline; }
      a:link  { color: #1a5c99; }
      a:visited { color: #1a5c99; }
      a:hover { text-decoration: none; color: black; }
      a img { border: none; }

  </style>
</head>
<body>
  <table id="container" width="80%" height="auto" align="left" cellspacing="0" cellpadding="0" border="0">
    <tr>      
      <td style="padding:15px;">
        <h1><?php echo mail_get_site_signature(); ?></h1>
      </td>
    </tr>
    <tr>
      <td style="padding:15px;">
        <?php echo $sf_content; ?>
      </td>
    </tr>  
  </table>
</body>
</html>
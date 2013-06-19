<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<title>Sift javascript unit tests</title>
<link rel="stylesheet" href="/_/screen.css">
</head>
<body>

<h1>
  <a class="brand" href="/">Sift JS unit tests</a>
</h1>

<ul>
  
<?php

foreach(new DirectoryIterator(dirname(__FILE__)) as $i)
{
  if($i->isDot() || !$i->isDir() || substr($i->getBasename(), 0, 1) == '_')
  {
    continue;
  }
?>
  <li>
    <a href="<?php echo $i->getBasename(); ?>"><?php echo str_replace('_', ' ', ucfirst($i)); ?></a>
  </li>
<?php
  
}

?>
  </ul>

</body>
</html>
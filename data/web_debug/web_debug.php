<!-- Sift web debug //-->
<div id="web-debug"></div>
<?php ob_start(); ?>
<?php $panelContents = array(); ?>
  <div id="web-debug-toolbar">
    <ul>
      <li id="web-debug-toolbar-status" class="<?php echo $status; ?>"></li>
      <li id="web-debug-logo">
        <a href="#" class="web-debug-toolbar-toggler"></a>
      </li>
<?php foreach($panels as $name => $panel): $icon = $panel->getIcon(); ?>
     <li id="web-debug-toolbar-panel-<?php echo str_replace('_', '-', $name); ?>" class="<?php echo $web_debug->getStatusClass($panel->getStatus()); ?><?php echo $name == $current ? ' current' : ''; ?>">
<?php if(($content = $panel->getPanelContent()) || $panel->getTitleUrl()): $panelContents[$name] = $panel; ?>
       <a data-panel="<?php echo $name; ?>" href="<?php echo $panel->getTitleUrl() ? $panel->getTitleUrl() : '#'; ?>" title="<?php echo htmlentities(strip_tags($panel->getTitle()), ENT_QUOTES); ?>"><?php echo $icon ? sprintf('<img src="%s" alt="%s" />', $icon, $panel->getTitle()) : ''; ?> <?php echo $panel->getTitle(); ?></a>
<?php else: ?>
      <span title="<?php echo htmlentities(strip_tags($panel->getTitle()), ENT_QUOTES); ?>"><?php echo $icon ? sprintf('<img src="%s" alt="%s" />', $icon, $panel->getTitle()) : ''; ?> <?php echo $panel->getTitle(); ?></span>
<?php endif; ?>
     </li>
<?php endforeach; ?>
   </ul>
 </div>
<?php foreach($panelContents as $name => $panel): ?>
  <div class="web-debug-panel<?php echo $name == $current ? ' web-debug-panel-active': ''; ?>" id="web-debug-panel-<?php echo $name; ?>">
    <div>
      <h2><?php echo $panel->getPanelTitle(); ?></h2>
      <?php echo $panel->getPanelContent(); ?>
    </div>
  </div>
<?php endforeach; ?>
<?php $content = ob_get_clean(); ?>
<script type="text/javascript" id="web-debug-javascript">
<?php echo $web_debug->getDebugJavascript(); ?>

(function(WebDebug)
{
  // WebDebug.$(window).bind('load', function()
  WebDebug.$(document).ready(function()
  {
    // provide the instance object to the plugins
    WebDebug.Instance = new WebDebug('<?php echo (base64_encode($content)); ?>',
      <?php echo json_encode($options); ?>);
  });
}(WebDebug));
</script>

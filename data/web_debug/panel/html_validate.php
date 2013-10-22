<div id="web-debug-html-validate"></div>
<pre><?php echo $content_highlighted; ?></pre>

<script type="text/javascript">
<!--
(function(WebDebug)
{
  var $ = WebDebug.$;
  WebDebug.Extensions.HtmlValidator = new WebDebug.HtmlValidator(
    '<?php echo base64_encode($content); ?>',
    '<?php echo $content_type; ?>',
    $('#web-debug-html-validate'), // result holder
    $('#web-debug-toolbar-panel-html-validate a') // panel info
  );
}(WebDebug));

//-->
</script>
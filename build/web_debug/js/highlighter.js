/**
 * Syntax highlighter
 *
 */
var Highlighter = (function() {

  var highlight = function(code)
  {
    return code.replace(/&/g, "&amp;").replace(/</g, "&lt;")
            .replace(/>/g, "&gt;").replace(/"/g, "&quot;")
            .replace(/'/g, "&#039;");
  };

  return {
    highlight: highlight
  };

})();
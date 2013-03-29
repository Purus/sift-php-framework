function __(s) {
	return typeof l10n[s] != 'undefined' ? l10n[s] : s;
}
function test(param) {
	var a = __("Hello world, testing jsgettext");
	func(__('Test string'));
	var reg1 = /"[a-z]+"/i;
	var reg2 = /[a-z]+\+\/"aa"/i;
	var s1 = __('string 1: single quotes');
	var s2 = __("string 2: double quotes");
	var s3 = __("/* comment in string */");
	var s4 = __("regexp in string: /[a-z]+/i");
	var s5 = jsgettext( "another function" );
	var s6 = avoidme("should not see me!");
	var s7 = __("string 2: \"escaped double quotes\"");
	var s8 = __('string 2: \'escaped single quotes\'');

	// "string in comment"
	//;

	/**
	 * multiple
	 * lines
	 * comment
	 * __("Hello world from comment")
	 */
}

/**
 * Encode special characters in a plain-text string for display as HTML.
 */
Application.checkPlain = function(str) {
  str = String(str);
  var replace = {
    '&': '&amp;',
    '"': '&quot;',
    '<': '&lt;',
    '>': '&gt;'
  };
  for (var character in replace) {
    var regex = new RegExp(character, 'g');
    str = str.replace(regex, replace[character]);
  }
  return str;
};

alert(__('Jesus is Lord!'));
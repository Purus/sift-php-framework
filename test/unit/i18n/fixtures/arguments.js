// this is catched successfully
// var string = __("Please enter %number% more characters.");

// this is problematic

var string = __("Please enter %number% more characters.");
var string = __("Please enter %number% more characters with arguments.", { '%number%' : 2 });

__("Please enter %number% more characters with arguments.");

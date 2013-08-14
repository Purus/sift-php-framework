/**
 * jQuery validate.password plug-in (for Sift PHP framework).
 *
 * The password strength matches the sfValidatorPassword strength routine)
 *
 * Dual licensed under the MIT and GPL licenses:
 *   http://www.opensource.org/licenses/mit-license.php
 *   http://www.gnu.org/licenses/gpl.html
 */
(function($, window) {

  var PasswordStrengthCalculator = function(options)
  {
    this.options = $.extend({
      minStrenght: 10
    }, {}, options);
  };

  function str_split(string, split_length)
  {
    // http://kevin.vanzonneveld.net
    // +     original by: Martijn Wieringa
    // +     improved by: Brett Zamir (http://brett-zamir.me)
    // +     bugfixed by: Onno Marsman
    // +      revised by: Theriault
    // +        input by: Bjorn Roesbeke (http://www.bjornroesbeke.be/)
    // +      revised by: Rafał Kukawski (http://blog.kukawski.pl/)
    // *       example 1: str_split('Hello Friend', 3);
    // *       returns 1: ['Hel', 'lo ', 'Fri', 'end']
    if(split_length === null) {
      split_length = 1;
    }
    if(string === null || split_length < 1) {
      return false;
    }
    string += '';
    var chunks = [],
            pos = 0,
            len = string.length;
    while(pos < len) {
      chunks.push(string.slice(pos, pos += split_length));
    }

    return chunks;
  }

  function ord(string) {
    // http://kevin.vanzonneveld.net
    // +   original by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
    // +   bugfixed by: Onno Marsman
    // +   improved by: Brett Zamir (http://brett-zamir.me)
    // +   input by: incidence
    // *     example 1: ord('K');
    // *     returns 1: 75
    // *     example 2: ord('\uD800\uDC00'); // surrogate pair to create a single Unicode character
    // *     returns 2: 65536
    var str = string + '',
            code = str.charCodeAt(0);
    if(0xD800 <= code && code
            <= 0xDBFF) { // High surrogate (could change last hex to 0xDB7F to treat high private surrogates as single characters)
      var hi = code;
      if(str.length === 1) {
        return code; // This is just a high surrogate with no following low surrogate, so we return its value;
        // we could also throw an error as it is not a complete character, but someone may want to know
      }
      var low = str.charCodeAt(1);
      return ((hi - 0xD800) * 0x400) + (low - 0xDC00) + 0x10000;
    }
    if(0xDC00 <= code && code <= 0xDFFF) { // Low surrogate
      return code; // This is just a low surrogate with no preceding high surrogate, so we return its value;
      // we could also throw an error as it is not a complete character, but someone may want to know
    }
    return code;
  }

  function count(mixed_var, mode) {
    // http://kevin.vanzonneveld.net
    // +   original by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
    // +      input by: Waldo Malqui Silva
    // +   bugfixed by: Soren Hansen
    // +      input by: merabi
    // +   improved by: Brett Zamir (http://brett-zamir.me)
    // +   bugfixed by: Olivier Louvignes (http://mg-crea.com/)
    // *     example 1: count([[0,0],[0,-4]], 'COUNT_RECURSIVE');
    // *     returns 1: 6
    // *     example 2: count({'one' : [1,2,3,4,5]}, 'COUNT_RECURSIVE');
    // *     returns 2: 6
    var key, cnt = 0;

    if(mixed_var === null || typeof mixed_var === 'undefined') {
      return 0;
    } else if(mixed_var.constructor !== Array && mixed_var.constructor
            !== Object) {
      return 1;
    }

    if(mode === 'COUNT_RECURSIVE') {
      mode = 1;
    }
    if(mode != 1) {
      mode = 0;
    }

    for(key in mixed_var) {
      if(mixed_var.hasOwnProperty(key)) {
        cnt++;
        if(mode == 1 && mixed_var[key] && (mixed_var[key].constructor === Array
                || mixed_var[key].constructor === Object)) {
          cnt += this.count(mixed_var[key], 1);
        }
      }
    }

    return cnt;
  }

  function array_unique(inputArr) {
    // http://kevin.vanzonneveld.net
    // +   original by: Carlos R. L. Rodrigues (http://www.jsfromhell.com)
    // +      input by: duncan
    // +   bugfixed by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
    // +   bugfixed by: Nate
    // +      input by: Brett Zamir (http://brett-zamir.me)
    // +   bugfixed by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
    // +   improved by: Michael Grier
    // +   bugfixed by: Brett Zamir (http://brett-zamir.me)
    // %          note 1: The second argument, sort_flags is not implemented;
    // %          note 1: also should be sorted (asort?) first according to docs
    // *     example 1: array_unique(['Kevin','Kevin','van','Zonneveld','Kevin']);
    // *     returns 1: {0: 'Kevin', 2: 'van', 3: 'Zonneveld'}
    // *     example 2: array_unique({'a': 'green', 0: 'red', 'b': 'green', 1: 'blue', 2: 'red'});
    // *     returns 2: {a: 'green', 0: 'red', 1: 'blue'}
    var key = '',
            tmp_arr2 = {},
            val = '';

    var __array_search = function(needle, haystack) {
      var fkey = '';
      for(fkey in haystack) {
        if(haystack.hasOwnProperty(fkey)) {
          if((haystack[fkey] + '') === (needle + '')) {
            return fkey;
          }
        }
      }
      return false;
    };

    for(key in inputArr) {
      if(inputArr.hasOwnProperty(key)) {
        val = inputArr[key];
        if(false === __array_search(val, tmp_arr2)) {
          tmp_arr2[key] = val;
        }
      }
    }

    return tmp_arr2;
  }

  PasswordStrengthCalculator.prototype = {
    constructor: PasswordStrengthCalculator,

    /**
     * Check a string for alphabetically ordered characters
     *
     * @param string $string
     * @param integer $number
     * @return boolean
     * @see http://stackoverflow.com/questions/12124803/check-a-string-for-alphabetically-ordered-characters
     */
    hasOrderedCharacters: function(string, number)
    {
      if(typeof number === 'undefined')
      {
        number = 3;
      }

      var length = string.length;
      var count = 0, last = 0, current;

      for(var i = 0; i < length; i++)
      {
        current = ord(string[i]);
        if(current == last + 1)
        {
          count++;
          if(count >= number)
          {
            return true;
          }
        }
        else
        {
          count = 1;
        }
        last = current;
      }
      return false;
    },

    /**
     * Return meter class name for given strength
     *
     * @param {Integer} strength
     * @returns {String}
     */
    getMeterClassName : function(strength)
    {
      if(strength < this.options.minStrength)
      {
        return 'weak';
      }
      else if(strength < 25)
      {
        return 'quite-good';
      }
      else if(strength < 50)
      {
        return 'good';
      }
      else if(strength < 75)
      {
        return 'strong';
      }

      return 'superb';
    },

    /**
     * Get rating for given password. This method mimics the behavior of sfValidatorPassword.
     *
     * @param {String} password
     * @returns {Object}
     */
    getStrength: function(password)
    {
      var strength = 0;
      var passwordLength = password.length;

      if(passwordLength > 9)
      {
        strength += 10;
      }

      var temp;
      for(var i = 2; i <= 4; i++)
      {
        temp = str_split(password, i);
        strength -= (Math.ceil(passwordLength / i) - count(array_unique(temp)));
      }

      var numbers = password.match(/[0-9]/g);
      if((numbers))
      {
        numbers = numbers.length;
        if(numbers >= 2)
        {
          strength += 5;
        }
      }
      else
      {
        numbers = 0;
      }

      if(this.hasOrderedCharacters(password))
      {
        strength -= 20;
      }

      var symbols = password.match(/[!@#$%^&*()_+{}:"<>?\|\[\];\',./`\~]/g);

      if((symbols))
      {
        symbols = symbols.length;
        if(symbols > 0)
        {
          strength += symbols * 7;
        }
      }
      else
      {
        symbols = 0;
      }

      var lowercaseCharacters = password.match(/[a-z]/g);
      var uppercaseCharacters = password.match(/[A-Z}]/g);
      var utf8Characters = password.match(/([\u0100-\u017F\u00C0-\u00CF\u00E0-\u00FF])/g);

      if((lowercaseCharacters))
      {
        lowercaseCharacters = lowercaseCharacters.length;
      }
      else
      {
        lowercaseCharacters = 0;
      }

      if((uppercaseCharacters))
      {
        uppercaseCharacters = uppercaseCharacters.length;
      }
      else
      {
        uppercaseCharacters = 0;
      }

      if((lowercaseCharacters > 0) && (uppercaseCharacters > 0))
      {
        strength += 10;
      }

      if((utf8Characters))
      {
        utf8Characters = utf8Characters.length;
      }
      else
      {
        utf8Characters = 0;
      }

      if(utf8Characters > 0)
      {
        strength += 10 * utf8Characters;
      }

      var characters = lowercaseCharacters + uppercaseCharacters
              + utf8Characters;

      if((numbers > 0) && (symbols > 0))
      {
        strength += 15;
      }

      if((numbers > 0) && (characters > 0))
      {
        strength += 15;
      }

      if((symbols > 0) && (characters > 0))
      {
        strength += 15;
      }

      if((numbers === 0) && (symbols === 0))
      {
        strength -= 10;
      }

      if((symbols === 0) && (characters === 0))
      {
        strength -= 10;
      }

      if(strength < 0)
      {
        strength = 0;
      }
      else if(strength > 100)
      {
        strength = 100;
      }

      return {
        strength: strength,
        isWeak: strength < this.options.minStrength,
        className: this.getMeterClassName(strength)
      };
    }
  };

  // export so other code can use it
  window.PasswordStrengthCalculator = PasswordStrengthCalculator;

  if($.type($.validator) !== 'undefined')
  {
    /**
     * Return password rating
     *
     * @param {String} password
     * @param {Object} options
     * @returns Object The rating result object
     */
    $.validator.passwordStrength = function(password, options)
    {
      var calculator = new PasswordStrengthCalculator(options);
      return calculator.getStrength(password);
    };

    /**
     * Add "password" validation method
     */
    $.validator.addMethod('passwordStrength', function(password, element, options)
    {
      if(this.optional(element))
      {
        return true;
      }

      var rating = $.validator.passwordStrength(password, options);
      var $element = $(element);
      var meter = $element.parent().find('.password-strength-meter');
      // make it the same as the element
      meter.width($element.width());

      meter.find('.password-strength-meter-bar')
           .removeClass()
           .addClass('password-strength-meter-bar')
           .addClass(rating.className)
           .css({
            // always show something
            width: (rating.strength > 0 ? rating.strength : 1) + '%'
          });

      return !rating.isWeak;
    }, 'Password is weak.');

    $.validator.classRuleSettings.passwordStrength = { 'password-strength': true };
  };


})(jQuery, window);

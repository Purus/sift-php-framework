/**
 * Custom callback method for jQuery Validation Plugin.
 * 
 * Usage:
 * 
 * fielName: {
 *   customCallback:
 *     callback: function(value, element, params) 
 *     {
 *        // validate value with my logic
 *        return true; 
 *     }
 * }
 *
 * Copyright (c) Mishal.cz <mishal at mishal dot cz>
 * @version SVN:$Id: jquery.validate.custom_callback.js 85 2012-11-30 14:11:01Z michal $
 */ 
jQuery.validator.addMethod('customCallback', function(value, element, params) 
{
  try {
    var myCallback = params.callback;    
    var result = myCallback.call(this, value, element, params);
  }
  catch(e)
  {
    this.settings.debug && window.console && console.log("exception occured when checking element " + element.id
						 + ", check the 'customCallback' method", e);    
    throw e;
  }
  return this.optional(element) || result;
}, 'Invalid value');

/*
 * Translated default messages for the jQuery validation plugin.
 * 
 * Locale: CS
 */
jQuery.extend(jQuery.validator.messages, {
  customCallback: 'Zadaná hodnota není správně.'
});

jQuery.validator.addMethod('notEqualTo', function(value, element, param) {
  // bind to the blur event of the target in order to revalidate whenever the target field is updated
  // TODO find a way to bind the event just once, avoiding the unbind-rebind overhead
  var target = $(param).unbind(".validate-notEqualTo").bind("blur.validate-notEqualTo", function() 
  {
    $(element).valid();
  });
  return value != target.val();
});

/*
 * Translated default messages for the jQuery validation plugin.
 * 
 * Locale: CS
 */
jQuery.extend(jQuery.validator.messages, {
  notEqualTo: 'Zadané hodnoty nesmí být stejné.'
});

jQuery.validator.addMethod('regexPattern', function(value, element, param) 
{
  // taken from Nette Framework.
  // Copyright (c) 2004, 2011 David Grudl (http://davidgrudl.com)
  var parts = typeof param === 'string' ? param.match(/^\/(.*)\/([imu]*)$/) : false;
  var result;
  if(parts)
  {   
    try 
    {    
      result = (new RegExp(parts[1], parts[2].replace('u', ''))).test(value);      
    } catch(e) 
    {
      this.settings.debug && window.console && console.log("exception occured when checking element " + element.id
               + ", check the 'regexPattern' method", e);    
      throw e;
    }
  }
  
  return this.optional(element) || result;  
});

jQuery.validator.addMethod('regexPatternNegative', function(value, element, param) 
{
  // taken from Nette Framework.
  // Copyright (c) 2004, 2011 David Grudl (http://davidgrudl.com)
  var parts = typeof param === 'string' ? param.match(/^\/(.*)\/([imu]*)$/) : false;
  var result;
  if(parts)
  {   
    try 
    {    
      // negative result!
      result = !(new RegExp(parts[1], parts[2].replace('u', ''))).test(value);            
    } catch(e)
    {
      this.settings.debug && window.console && console.log("exception occured when checking element " + element.id
               + ", check the 'regexPatternNegative' method", e);    
      throw e;
    }
  }  
  return this.optional(element) || result;  
});

/*
 * Translated default messages for the jQuery validation plugin.
 * 
 * Locale: CS
 */
jQuery.extend(jQuery.validator.messages, {
  regexPattern: 'Zadaná hodnota není správně.'
});

// http://stackoverflow.com/questions/6212946/jquery-validate-file-upload
jQuery.validator.addMethod('fileSize', function(value, element, param) {
    // param = size (en bytes) 
    // element = element to validate (<input>)
    // value = value of the element (file name)
    return this.optional(element) || (element.files[0].size <= param) 
});

/*
 * Translated default messages for the jQuery validation plugin.
 * 
 * Locale: CS
 */
jQuery.extend(jQuery.validator.messages, {
  fileSize: 'Soubor je příliš velký.'
});
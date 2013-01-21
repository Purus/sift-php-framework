
function sfWebDebugGetElementsByClassName(strClass, strTag, objContElm)
{
  // http://muffinresearch.co.uk/archives/2006/04/29/getelementsbyclassname-deluxe-edition/
  strTag = strTag || "*";
  objContElm = objContElm || document;
  var objColl = (strTag == '*' && document.all) ? document.all : objContElm.getElementsByTagName(strTag);
  var arr = new Array();
  var delim = strClass.indexOf('|') != -1  ? '|' : ' ';
  var arrClass = strClass.split(delim);
  var j = objColl.length;
  for (var i = 0; i < j; i++) {
    if(objColl[i].className == undefined) continue;
    var arrObjClass = objColl[i].className.split ? objColl[i].className.split(' ') : [];
    if (delim == ' ' && arrClass.length > arrObjClass.length) continue;
    var c = 0;
      comparisonLoop:
      {
        var l = arrObjClass.length;
        for (var k = 0; k < l; k++) {
          var n = arrClass.length;
          for (var m = 0; m < n; m++) {
            if (arrClass[m] == arrObjClass[k]) c++;
            if (( delim == '|' && c == 1) || (delim == ' ' && c == arrClass.length)) {
              arr.push(objColl[i]);
              break comparisonLoop;
            }
          }
        }
      }
  }
  return arr;
}

function sfWebDebugToggleMenu()
{
  var element = document.getElementById('sfWebDebugDetails');

  var cacheElements = sfWebDebugGetElementsByClassName('sf-web-debug-cache');
  var mainCacheElements = sfWebDebugGetElementsByClassName('sf-web-debug-action-cache');
  var panelElements = sfWebDebugGetElementsByClassName('sfWebDebugTop');

  if (element.style.display != 'none')
  {
    for (var i = 0; i < panelElements.length; ++i)
    {
      panelElements[i].style.display = 'none';
    }

    // hide all cache information
    for (var i = 0; i < cacheElements.length; ++i)
    {
      cacheElements[i].style.display = 'none';
    }
    for (var i = 0; i < mainCacheElements.length; ++i)
    {
      mainCacheElements[i].style.border = 'none';
    }
  }
  else
  {
    for (var i = 0; i < cacheElements.length; ++i)
    {
      cacheElements[i].style.display = '';
    }
    for (var i = 0; i < mainCacheElements.length; ++i)
    {
      mainCacheElements[i].style.border = '1px solid #f00';
    }
  }

  sfWebDebugToggle('sfWebDebugDetails');
  sfWebDebugToggle('sfWebDebugShowMenu');
  sfWebDebugToggle('sfWebDebugHideMenu');
}

function sfWebDebugToggleCookie()
{
  var cookie = sfWebDebugGetCookie('sf_web_debug');
  if(cookie)
  {
    sfWebDebugSetCookie('sf_web_debug', '', -1, '/');
  }
  else
  {
    
    // set cookie to save the setting!
    sfWebDebugSetCookie('sf_web_debug', '1', 365, '/');  
  }

}

function sfWebDebugShowDetailsFor(element)
{
  if (typeof element == 'string')
    element = document.getElementById(element);

  var panelElements = sfWebDebugGetElementsByClassName('sfWebDebugTop');
  for (var i = 0; i < panelElements.length; ++i)
  {
    if (panelElements[i] != element)
    {
      panelElements[i].style.display = 'none';
    }
  }

  sfWebDebugToggle(element);
}

function sfWebDebugToggle(element)
{
  if (typeof element == 'string')
    element = document.getElementById(element);

  if (element)
    element.style.display = element.style.display == 'none' ? '' : 'none';
}

function sfWebDebugToggleMessages(klass)
{
  var elements = sfWebDebugGetElementsByClassName(klass);

  var x = elements.length;
  for (var i = 0; i < x; ++i)
  {
    sfWebDebugToggle(elements[i]);
  }
}

function sfWebDebugToggleAllLogLines(show, klass)
{
  var elements = sfWebDebugGetElementsByClassName(klass);
  var x = elements.length;
  for (var i = 0; i < x; ++i)
  {
    elements[i].style.display = show ? '' : 'none';
  }
}

function sfWebDebugShowOnlyLogLines(type)
{
  var types = new Array();
  types[0] = 'info';
  types[1] = 'warning';
  types[2] = 'error';
  for (klass in types)
  {
    var elements = sfWebDebugGetElementsByClassName('sfWebDebug' + types[klass].substring(0, 1).toUpperCase() + types[klass].substring(1, types[klass].length));
    var x = elements.length;
    for (var i = 0; i < x; ++i)
    {
      if ('tr' == elements[i].tagName.toLowerCase())
      {
        elements[i].style.display = (type == types[klass]) ? '' : 'none';
      }
    }
  }
}

function sfWebDebugSetCookie(name, value, expires, path, domain, secure)
{

  // set time, it's in milliseconds
  var today = new Date();
  today.setTime(today.getTime());

  /*
  if the expires variable is set, make the correct
  expires time, the current script below will set
  it for x number of days, to make it for hours,
  delete * 24, for minutes, delete * 60 * 24
  */
  if(expires)
  {
    expires = expires * 1000 * 60 * 60 * 24;
  }
  var expires_date = new Date( today.getTime() + (expires) );

  document.cookie = name + "=" +escape( value ) +
  ( ( expires ) ? ";expires=" + expires_date.toGMTString() : "" ) +
  ( ( path ) ? ";path=" + path : "" ) +
  ( ( domain ) ? ";domain=" + domain : "" ) +
  ( ( secure ) ? ";secure" : "" );
}

function sfWebDebugGetCookie(check_name) {
  // first we'll split this cookie up into name/value pairs
  // note: document.cookie only returns name=value, not the other components
  var a_all_cookies = document.cookie.split( ';' );
  var a_temp_cookie = '';
  var cookie_name = '';
  var cookie_value = '';
  var b_cookie_found = false; // set boolean t/f default f

  for ( i = 0; i < a_all_cookies.length; i++ )
  {
    // now we'll split apart each name=value pair
    a_temp_cookie = a_all_cookies[i].split( '=' );


    // and trim left/right whitespace while we're at it
    cookie_name = a_temp_cookie[0].replace(/^\s+|\s+$/g, '');

    // if the extracted name matches passed check_name
    if ( cookie_name == check_name )
    {
      b_cookie_found = true;
      // we need to handle case where cookie has no value but exists (no = sign, that is):
      if ( a_temp_cookie.length > 1 )
      {
        cookie_value = unescape( a_temp_cookie[1].replace(/^\s+|\s+$/g, '') );
      }
      // note that in cases where cookie is initialized but no value, null is returned
      return cookie_value;
      break;
    }
    
    a_temp_cookie = null;
    cookie_name = '';
  }

  if(!b_cookie_found)
  {
    return null;
  }

}

// update with cookie
var sf_web_debug_cookie = sfWebDebugGetCookie('sf_web_debug');
if(sf_web_debug_cookie && sf_web_debug_cookie == 1)
{
  sfWebDebugToggleMenu();
}

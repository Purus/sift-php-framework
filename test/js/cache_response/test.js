
module("Cache response Test");

$.ajaxCacheResponse.storage.clear();

function isNotFromCache(result)
{
  ok(result === false, 'Data is loaded from server');
};

function isFromCache(result)
{
  ok(result === true, 'Data is refreshed from cache');
};

function isValidJsonResponse(result)
{
  ok(result.response == 'success', 'Result is valid JSON result');
};

function isValidXmlResponse(result)
{
  var xml = $(result);
  ok(xml.find('response:first').attr('value') == 'success', 'Result is valid XML result');
};

test("Json request", function()
{
  // make the request
  $.ajax({
    url: 'fixtures/ajax-response.json',
    cacheResponse: true,
    async: false,
    success: function(result, textStatus, jqXhr)
    {
      isNotFromCache(jqXhr.responseFromCache);
      isValidJsonResponse(result);
    }
  });

  // make second request, which should be cached
  $.ajax({
    url: 'fixtures/ajax-response.json',
    cacheResponse: true,
    async: false,
    success: function(result, textStatus, jqXhr)
    {
      isFromCache(jqXhr.responseFromCache);
      isValidJsonResponse(result);
    }
  });
});

test("Xml request", function()
{
  // make the request
  $.ajax({
    url: 'fixtures/ajax-response.xml',
    dataType: 'xml',
    cacheResponse: true,
    async: false,
    success: function(result, textStatus, jqXhr)
    {
      isNotFromCache(jqXhr.responseFromCache);
      isValidXmlResponse(result);
    }
  });

  // make second request, which should be cached
  $.ajax({
    url: 'fixtures/ajax-response.xml',
    dataType: 'xml',
    cacheResponse: true,
    async: false,
    success: function(result, textStatus, jqXhr)
    {
      isFromCache(jqXhr.responseFromCache);
      isValidXmlResponse(result);
    }
  });
});

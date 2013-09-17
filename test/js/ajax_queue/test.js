/*
  ======== A Handy Little QUnit Reference ========
  http://docs.jquery.com/QUnit

  Test methods:
    expect(numAssertions)
    stop(increment)
    start(decrement)
  Test assertions:
    ok(value, [message])
    equal(actual, expected, [message])
    notEqual(actual, expected, [message])
    deepEqual(actual, expected, [message])
    notDeepEqual(actual, expected, [message])
    strictEqual(actual, expected, [message])
    notStrictEqual(actual, expected, [message])
    raises(block, [expected], [message])
*/

module("Ajax queue", {
  setup: function()
  {
    this.xhr = sinon.useFakeXMLHttpRequest();
    var requests = this.requests = [];
    this.xhr.onCreate = function(xhr)
    {
      requests.push(xhr);
    };
  },
  teardown: function()
  {
    this.xhr.restore();
  }
});

test('$.ajaxQueue is a function', function()
{
  ok(typeof $.ajaxQueue === 'function');
});

test('Async requests', 6, function()
{
  var callback = callback2 = completeCallback = completeCallback2 = sinon.spy();

  jQuery.ajaxQueue({
    url: 'fake',
    dataType: 'json',
    async: true,
    complete: completeCallback
  }).done(callback);

  jQuery.ajaxQueue({
    url: 'fake',
    dataType: 'json',
    async: true,
    complete: completeCallback2
  }).done(callback2);

  // only one request
  ok(this.requests.length == 1, 'first request has been made');

  // respond on the first request
  this.requests[0].respond(200, { "Content-Type": "application/json" }, '{ "response": "first" }');
  ok(callback.calledWith({response: "first" }), 'done() has been called for the first request');

  // first request is completed, second has been added
  ok(this.requests.length == 2, 'Two requests has been made');

  // respond on the first request
  this.requests[1].respond(200, { "Content-Type": "application/json" }, '{ "response": "second" }');
  ok(callback.calledWith({response: "second" }), 'done() callback has been called');

  ok(completeCallback.called, "complete callback has been called for the first request");
  ok(completeCallback2.called, "complete callback has been called for the second request");
});
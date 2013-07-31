module("Load mask");

test("setLoading", function() {
  $('#test-div').setLoading(true);
  ok($('#test-div').hasClass('load-masked') == true, 'Element is assigned load-masked CSS class.');
  ok($('#test-div').find('.load-mask').length == 1, 'Mask is appended to the element.');
  ok($('#test-div').find('.load-mask').text().trim() == 'Loading...', 'Default message is placed in the mask.');
  ok($('#test-div').find('.load-mask').is(':visible') === true, 'Mask is visible.');
  $('#test-div').setLoading(false);
  ok($('#test-div').find('.load-mask').is(':visible') === false, 'Mask is hidden.');
});

test("destroy", function() {
  $('#test-div').setLoading('destroy');
  ok($('#test-div').find('.load-mask').length === 0, 'Mask is removed.');
});

test("setMessage", function() {
  $('#test-div').setLoading('message', 'Loaded.');
  ok($('#test-div').find('.load-mask').text().trim() == 'Loaded.', 'Message is placed in the mask.');
});

test("simulate ajax", function() {

  $('#test-div').setLoading(true);
  ok($('#test-div').find('.load-mask').is(':visible') === true, 'Mask is visible.');
  $('#test-div').setLoading(false);
  ok($('#test-div').find('.load-mask').is(':visible') === false, 'Mask is visible.');
  $('#test-div').setLoading(true);
  ok($('#test-div').find('.load-mask').is(':visible') === true, 'Mask is visible.');
});


test("overlaySize", function() {

  $('#test-div').setLoading('destroy').setLoading({
    overlaySize: {
      width: 100,
      height: 100
    },
    msg: 'Foobar'
  });
  ok($('#test-div').find('.load-mask').text().trim() == 'Foobar', 'Message is placed in the mask.');
  ok($('#test-div').find('.load-mask').width() == 100, 'Width is set correctly');
  ok($('#test-div').find('.load-mask').height() == 100, 'Height is set correctly');
});

QUnit.done(function(details)
{
  $('#test-div').remove()
});
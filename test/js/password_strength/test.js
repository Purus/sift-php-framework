module("Password strength meter");

test("has ordered characters", function()
{
  var c = new PasswordStrengthCalculator();

  var tests = [
    '123456*', 'ahoj*', 'foo123>', 'abcdeFg', 'secret1password', 'žížala'
  ];

  // extracted from php unit tests
  // see sfValidatorPassword unit test
  var expectedRating = [
    true,
    false,
    true,
    true,
    false,
    false
  ];

  for(var i = 0; i < tests.length; i++)
  {
    equal(c.hasOrderedCharacters(tests[i]), expectedRating[i], 'hasOrderedCharacters() returns ' + (expectedRating[i] ? ('true') : ('false')) + ' as expected for ' + tests[i]);
  }

});

test("validator method works ok", function() {

  var rating = $.validator.passwordStrength('test');

  ok($.type(rating), 'object', "passwordRating returns an object");

  var tests = [
    '123456', '1234^56', 'ahoj*', 'foo123>', 'abcdeFg', 'secret1password', 'RaNd$mPJ1~', 'agent007', 'žitonapoliČºÀ'
  ];

  // extracted from php unit tests
  // see sfValidatorPassword unit test
  var expectedRating = [
    0,
    7,
    22,
    37,
    0,
    25,
    79,
    20,
    30
  ];

  for(var i = 0; i < tests.length; i++)
  {
    var rating = $.validator.passwordStrength(tests[i]);
    equal(rating.strength, expectedRating[i], 'passwordRating() returns rate ' + expectedRating[i] + ' as expected for ' + tests[i]);
  }

});
module("Request Storage Test");

test("RequestStorage.setItem() - set item", function() {
	expect(1);

	RequestStorage.setItem("foo", "bar");
	RequestStorage.setItem("baz", "lala");

	strictEqual(RequestStorage.length, 2,
			"Expected length was 2, but was actually: [" + RequestStorage.length + "]");

	RequestStorage.clear();
});

test("RequestStorage.getItem() - get item", function() {
	expect(1);

	RequestStorage.setItem("foo", "bar");
	var result = RequestStorage.getItem("foo");

	strictEqual(result, "bar",
			"Expected value was \"bar\", but was actually: [" + result + "]");

	RequestStorage.clear();
});

test("RequestStorage.removeItem() - remove item", function() {
	expect(1);

	RequestStorage.setItem("foo", "bar");
	RequestStorage.removeItem("foo");

	strictEqual(RequestStorage.length, 0,
			"Expected length was 0, but was actually: [" + RequestStorage.length + "]");

	RequestStorage.clear();
});

test("RequestStorage.clear() - clear", function() {
	expect(2);

	RequestStorage.setItem("foo", "bar");

	strictEqual(RequestStorage.length, 1,
			"Expected length was 1, but was actually: [" + RequestStorage.length + "]");

	RequestStorage.clear();

	strictEqual(RequestStorage.length, 0,
			"Expected length was 0, but was actually: [" + RequestStorage.length + "]");

	RequestStorage.clear();
});

test("RequestStorage.length - length", function() {
	expect(5);

	strictEqual(RequestStorage.length, 0,
			"Expected length was 0, but was actually: [" + RequestStorage.length + "]");

	RequestStorage.setItem("foo", "bar");
	RequestStorage.setItem("foo", "bar");

	strictEqual(RequestStorage.length, 1,
			"Expected length was 1, but was actually: [" + RequestStorage.length + "]");

	RequestStorage.removeItem("does-not-exist", "foobar");
	RequestStorage.removeItem("does-not-exist", "foobar");

	strictEqual(RequestStorage.length, 1,
			"Expected length was 1, but was actually: [" + RequestStorage.length + "]");

	RequestStorage.setItem("dog", "golden ret");
	RequestStorage.setItem("dog", "poodle");

	strictEqual(RequestStorage.length, 2,
			"Expected length was 2, but was actually: [" + RequestStorage.length + "]");

	RequestStorage.removeItem("dog");
	RequestStorage.removeItem(null);

	strictEqual(RequestStorage.length, 1,
			"Expected length was 1, but was actually: [" + RequestStorage.length + "]");

	RequestStorage.clear();
});

test("RequestStorage.key() - key", function() {
	expect(10);

	RequestStorage.setItem("foo", "bar");

	var key = RequestStorage.key(0);

	strictEqual(key, "foo",
			"Expected key was \"foo\", but was actually: [" + key + "]");

	var nullKeys = [-1, 2, "foo", true, false, {key: 1}, null, undefined, ""];

	for (var i = 0; i < nullKeys.length; i++) {
		key = RequestStorage.key(nullKeys[i]);
		strictEqual(key, null,
			"Expected key was null, but was actually: [" + key + "]");
	}

	RequestStorage.clear();
});
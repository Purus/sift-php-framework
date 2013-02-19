<?php

require_once(dirname(__FILE__) . '/../../bootstrap/unit.php');

$t = new lime_test(18, new lime_output_color());

class myBrowseHistory extends sfBrowseHistory {

}

$history = new myBrowseHistory();

$t->diag('public API');

$t->isa_ok(count($history), 'integer', 'count() returns integer');
$t->isa_ok($history->hasHistory(), 'boolean', '->hasHistory() returns boolean');
$t->isa_ok($history->getItems(), 'array', '->getItems() returns array');

$t->diag('push()');

$history->push(1, 'foobar');

$t->is(count($history), 1, 'item is pushed to the history');

$t->diag('pushItem()');

$history->pushItem(new sfBrowseHistoryItem(2, 'foobar2'));

$t->is(count($history), 2, 'item is pushed to the history');

$t->diag('delete()');

$history->delete(1);

$t->is(count($history), 1, 'item is deleted from the history');
$items = $history->getItems();

$t->is($items[0]->getId(), 2, 'item is still in the history');

$history->clear();

// we add 20 items, but maxItems is 10
for($i = 1; $i <= 20; $i++)
{
  $history->push($i, 'test');
}

$t->is(count($history), 10, 'History holds only 10 items');

$items = $history->getItems();

$i = 20;
foreach($items as $item)
{
  $t->is($item->getId(), $i, sprintf('History holds last %s. item', $i));
  $i--;
}

<?php

declare(strict_types=1);

$array = [1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15];
$splitter = new FlexibleArraySplitter($array);

// Example 1: Split with first group of 2 elements
$splitter->split(['first' => 2]);

echo 'First group: ';
print_r($splitter->get('first'));
// Output: [1, 2]

echo 'Remaining group: ';
print_r($splitter->getRemaining());
// Output: [3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15]

// Example 2: Split with first group of 3, second of 5, and 2 auto groups
$splitter->split([
    'primary' => 3,
    'secondary' => 5,
], 2);

echo 'Primary group: ';
print_r($splitter->get('primary'));
// Output: [1, 2, 3]

echo 'Secondary group: ';
print_r($splitter->get('secondary'));
// Output: [4, 5, 6, 7, 8]

echo 'First auto group: ';
print_r($splitter->getAutoGroup(1));
// Output: [9, 10, 11, 12]

echo 'Second auto group: ';
print_r($splitter->getAutoGroup(2));
// Output: [13, 14, 15]

// Example 3: Using simple method
$splitter->splitSimple(2, null, 3);

echo 'First group: ';
print_r($splitter->getFirst());
// Output: [1, 2]

echo 'All groups: ';
print_r($splitter->getAllGroups());

// Example 4: Method chaining
$firstGroup = (new FlexibleArraySplitter(range(1, 20)))
    ->split(['featured' => 4], 3)
    ->get('featured');

print_r($firstGroup);
// Output: [1, 2, 3, 4]

// Example 5: Get all auto groups
$splitter->split(['first' => 3], 3);
$autoGroups = $splitter->getAutoGroups();
print_r($autoGroups);

// Example 6: Access by index
$splitter->split(['header' => 2, 'body' => 5], 2);
echo 'First group: ';
print_r($splitter->getGroupByIndex(0)); // header group
echo 'Second group: ';
print_r($splitter->getGroupByIndex(1)); // body group

// Example 7: Utility methods
echo 'Number of groups: ' . $splitter->getGroupCount();
echo 'Group names: ';
print_r($splitter->getGroupNames());
echo "Has 'body' group? " . ($splitter->hasGroup('body') ? 'Yes' : 'No');
echo 'Last group: ';
print_r($splitter->getLastGroup());
<?php

declare(strict_types=1);

// Execute with specific fetch options
$result = $queryResult
    ->execute(['mode' => 'class', 'class' => ProductEntity::class])
    ->getAll();

// Or use fluent interface
$products = $queryResult->execute()->getAll();
$firstProduct = $queryResult->execute()->getFirst();
$count = $queryResult->execute()->count();

// Key-value pairs for dropdowns
$categories = $queryResult->execute()->getKeyPairs();

// Single column
$names = $queryResult->execute()->getColumn(1);

// Iteration support
foreach ($queryResult->execute() as $row) {
    // Process each row
}



// Old style still works but emits deprecation notices
$result = $queryResult->getResults('class', ProductEntity::class)->all();
$single = $queryResult->getResults()->single();


$allEntities = $model->all()->asClass();                    // All records as entities
$firstEntity = $model->first()->asClass();                  // First entity
$lastEntity = $model->last()->asClass();                    // Last entity

// With limits
$first5Entities = $model->first([], 5)->asClass();          // First 5 entities
$last3Entities = $model->last([], 3)->asClass();            // Last 3 entities

// Pagination
$page2Entities = $model->page(2, 10)->asClass();            // Page 2, 10 per page

// Get specific number
$tenEntities = $model->get(10)->asClass();                  // Get 10 entities

// With filtering
$activeEntities = $model->all(['status' => 'active'])->asClass();
$recentFirst = $model->first(['created_at >' => '2024-01-01'])->asClass();

// Pagination - only fetches 10 records from database
$page2Entities = $model->page(2, 10)->asClass();

// Get limited records - only fetches 5 records
$recentEntities = $model->get(5)->asClass();

// First records with filtering - only fetches 3 records
$first3Active = $model->first(['status' => 'active'], 3)->asClass();

// Filtered pagination - efficient database query
$activePage2 = $model->page(2, 10, ['status' => 'active'])->asClass();
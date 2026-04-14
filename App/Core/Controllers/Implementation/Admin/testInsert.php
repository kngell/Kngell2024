<?php

declare(strict_types=1);

/** @var TestProduct */
$entity = App::diget(TestProduct::class);
$entity->assign(['name' => 'Dj menguele', 'quantity' => 50, 'slug' => 'slug_test', 'price' => .2514565]);
$this->query->getEntityManager()->setEntity($entity);
$query = $this->query;
$q1 = $query->insert($entity)->build();
BrowserLogger::log('Parameters q1 ' . print_r($query->getParameters(), true));

$q2 = $query->insert()->build();
BrowserLogger::log('Query q2 ' . print_r($q2, true));
BrowserLogger::log('Parameters q2 ' . print_r($query->getParameters(), true));

$q3 = $query->insert(['name' => 'Dj menguele', 'quantity' => 50, 'slug' => 'slug_test', 'price' => .2514565])->build();
BrowserLogger::log('Query q3 ' . print_r($q3, true));
BrowserLogger::log('Parameters q3 ' . print_r($query->getParameters(), true));

$q4 = $query->insert('name', 'Dj menguele', 'quantity', 50, 'slug', 'slug_test', 'price', .2514565)->build();
BrowserLogger::log('Query q4 ' . print_r($q4, true));
BrowserLogger::log('Parameters q4 ' . print_r($query->getParameters(), true));

$q5 = $query->insert()->into('test_product')->columns(['name', 'quantity', 'slug', 'price'])->values(['Dj menguele',  50,  'slug_test',  .2514565])->build();
BrowserLogger::log('Query q5 ' . print_r($q5, true));
BrowserLogger::log('Parameters q5 ' . print_r($query->getParameters(), true));

$q6 = $query->insert()->into('test_product')->columns('name', 'quantity', 'slug', 'price')->values('Dj menguele', 50, 'slug_test', .2514565)->build();
BrowserLogger::log('Query q6 ' . print_r($q6, true));
BrowserLogger::log('Parameters q6 ' . print_r($query->getParameters(), true));

$q7 = $query->insert('name', 'quantity', 'slug', 'price')->into('test_product')->values('Dj menguele', 50, 'slug_test', .2514565)->build();
BrowserLogger::log('Query q7 ' . print_r($q7, true));
BrowserLogger::log('Parameters q7 ' . print_r($query->getParameters(), true));

$q8 = $query->insert('name', 'quantity', 'slug', 'price')->values('Dj menguele', 50, 'slug_test', .2514565)->build();
BrowserLogger::log('Query q8 ' . print_r($q8, true));
BrowserLogger::log('Parameters q8 ' . print_r($query->getParameters(), true));

$entity1 = clone $entity;
$entity1 = $entity1->assign(['name' => 'Dj menguele', 'quantity' => 50, 'slug' => 'slug_test', 'price' => .2514565]);
$entity2 = clone $entity;
$entity2 = $entity2->assign(['name' => 'EK JT', 'quantity' => 80, 'slug' => 'slug_test2', 'price' => .14536]);
$entity3 = clone $entity;
$entity3 = $entity3->assign(['name' => 'MMPP', 'quantity' => 1000, 'slug' => 'slug_gg', 'price' => 1452.789]);

$q9 = $query->insert($entity1, $entity2, $entity3)->build();
BrowserLogger::log('Query q9 ' . print_r($q9, true));
BrowserLogger::log('Parameters q9 ' . print_r($query->getParameters(), true));
BrowserLogger::display();
exit;
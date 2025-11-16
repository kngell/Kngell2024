<?php

declare(strict_types=1);

// $entity = App::diget(TestProduct::class);
// $this->query->getEntityManager()->setEntity($entity);
// $subquery = $this->query->select('code')->from('currency')->where('id', 2);
// $query = $this->query;
// // $arg = null;
// $q = $query->select('col1', ['col2'])
//     ->from('product')
//     ->leftJoin('region', ['id', 'name', 'code'])
//     ->on('pdt_id', 'region.product_id', 'name', 'r_name')
//     ->where('mane', 'name_value', ['colkey1', 'colvalue1'])
//     ->orWhere('keyOr', 'valueOr')
//     ->whereIn('currency', $subquery)
//     ->where(function ($query) {
//         $query->where('A', 'B')->orWhere('B', 'C');
//     })
//     ->where('status', 'active', function ($query) {
//         $query->where('override', true);
//     })
//     ->where(function ($query) {
//         $query->where('A', 'X')
//               ->orWhere(function ($q) {
//                   $q->where('B', 'D')->where('C', 'F');
//               });
//     })->build();

// $parameters = $this->query->getParameters();
// BrowserLogger::log('Query: ' . print_r($q, true));
// BrowserLogger::log('Parameters: ' . print_r($parameters, true));
// BrowserLogger::display();
// exit;
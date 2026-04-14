<?php

declare(strict_types=1);

$formData = [
    'min_price' => 0,
    'max_price' => 1000,
    'price_ranges' => [
        'brackets' => [
            ['min' => 0, 'max' => 250, 'label' => '$0 - $250'],
            ['min' => 250, 'max' => 500, 'label' => '$250 - $500'],
            ['min' => 500, 'max' => 750, 'label' => '$500 - $750'],
            ['min' => 750, 'max' => null, 'label' => '$750+'],
        ],
    ],
];
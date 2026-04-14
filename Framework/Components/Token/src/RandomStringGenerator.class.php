<?php

declare(strict_types=1);
// /**
//  * Class RandomStringGenerator.
//  */
// abstract class RandomStringGenerator
// {
//     use RandomGeneratorTrait;
//     use UrlSafeEncodingTrait;

//     /** @var string */
//     protected $alphabet;

//     /** @var int */
//     protected $alphabetLength;

//     /**
//      * @param string $alphabet
//      */
//     public function setAlphabet($alphabet)
//     {
//         if ('' !== $alphabet) {
//             $this->alphabet = $alphabet;
//         } else {
//             $this->alphabet =
//                 implode(range('a', 'z'))
//                 . implode(range('A', 'Z'))
//                 . implode(range(0, 9));
//         }
//         $this->alphabetLength = strlen($this->alphabet);
//     }

//     public function getAlphabet()
//     {
//         return $this->alphabet;
//     }
// }
<?php

declare(strict_types=1);

namespace App;

interface IGrabber
{


    /**
     * @param  string $productId
     * @return float
     */
    public function getPrice(string $productId): float;


    /**
     * @return string
     */
    public function getProductName(): string;


    /**
     * @return string
     */
    public function getRating(): string;


}//end interface

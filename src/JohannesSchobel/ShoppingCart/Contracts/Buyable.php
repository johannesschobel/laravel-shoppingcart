<?php

namespace JohannesSchobel\ShoppingCart\Contracts;

use Money\Money;

interface Buyable
{
    /**
     * Get the identifier of the Buyable item.
     *
     * @return int|string
     */
    public function getBuyableIdentifier($options = null);

    /**
     * Get the description or title of the Buyable item.
     *
     * @return string
     */
    public function getBuyableDescription($options = null);

    /**
     * Get the price of the Buyable item.
     *
     * @return Money
     */
    public function getBuyablePrice($options = null);

    /**
     * Get the type of the Buyable item.
     *
     * @return mixed
     */
    public function getBuyableType($options = null);
}
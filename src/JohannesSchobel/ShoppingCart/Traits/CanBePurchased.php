<?php

namespace JohannesSchobel\ShoppingCart\Traits;

use Money\Money;

trait CanBePurchased
{
    /**
     * Get the identifier of the Buyable item.
     *
     * @return int|string
     */
    public function getBuyableIdentifier($options = null)
    {
        return method_exists($this, 'getKey') ? $this->getKey() : $this->id;
    }

    /**
     * Get the description or title of the Buyable item.
     *
     * @return string|null
     */
    public function getBuyableDescription($options = null)
    {
        if (property_exists($this, 'name'))         return $this->name;
        if (property_exists($this, 'title'))        return $this->title;
        if (property_exists($this, 'description'))  return $this->description;

        return null;
    }

    /**
     * Get the price of the Buyable item.
     *
     * @return Money|null
     */
    public function getBuyablePrice($options = null)
    {
        if (property_exists($this, 'price'))        return $this->price;
        if (property_exists($this, 'cost'))         return $this->cost;
        if (property_exists($this, 'value'))        return $this->value;

        return null;
    }

    /**
     * Get the type of the Buyable item.
     *
     * @return string
     */
    public function getBuyableType($options = null)
    {
        return 'products';
    }
}
<?php

namespace JohannesSchobel\ShoppingCart\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Config;
use JohannesSchobel\ShoppingCart\Contracts\Buyable;
use JohannesSchobel\ShoppingCart\Exceptions\InvalidShoppingCartRowException;
use Money\Currencies\ISOCurrencies;
use Money\Formatter\DecimalMoneyFormatter;
use Money\Money;

class ShoppingCart extends Model
{
	const DEFAULT_NAME = 'default';
	
    protected $table = 'shoppingcarts';

    protected $fillable = [
        'identifier',
        'name',
        'content',
    ];

    protected $hidden = [];

    protected $casts = [];

    protected $dates = [
        'created_at',
        'updated_at',
    ];

    /**
     * Load a cart from the database. If no cart exists, an empty cart is returned
     *
     * @param array|string $identifier
     * @param null         $name
     *
     * @return mixed
     */
    public function load($identifier, $name = null)
    {
        $name = $name ?: self::DEFAULT_NAME;

        $classname = config('shoppingcart.models.shoppingcart');
        $shoppingcart = $classname::firstOrNew($this->defaultValues($identifier, $name));

        return $shoppingcart;
    }

    /**
     * Add an item to the cart.
     *
     * @param mixed     $id
     * @param mixed     $name
     * @param mixed     $type
     * @param int|float $qty
     * @param Money     $price
     * @param array     $options
     *
     * @return \JohannesSchobel\ShoppingCart\Models\ShoppingCart
     */
    public function addItem($id, $name = null, $type = null, $qty = 1, $price = null, array $options = [])
    {
        $cartItem = CartItem::fromAttributes($id, $name, $type, $price, $options);

        return $this->addItemToCart($cartItem, $qty);
    }

    /**
     * @param Buyable $item
     * @param int     $qty
     * @param array   $options
     *
     * @return ShoppingCart
     */
    public function addBuyable(Buyable $item, $qty = 1, array $options = [])
    {
        $cartItem = CartItem::fromBuyable($item, $options);

        return $this->addItemToCart($cartItem, $qty);
    }

    /**
     * @param CartItem $cartItem
     * @param int      $qty
     *
     * @return $this
     */
    private function addItemToCart(CartItem $cartItem, $qty = 1)
    {
        $cartItem->setQuantity($qty);
        $cartItem->setTaxRate(config('shoppingcart.tax'));

        $content = $this->getContent();

        if ($content->has($cartItem->rowId)) {
            $cartItem->setQuantity($cartItem->getQuantity() + $content->get($cartItem->rowId)->qty);
        }

        $content->put($cartItem->rowId, $cartItem);

        $this->content = serialize($content);

        $this->save();

        return $this;
    }

    /**
     * Remove a specified row from the shoppingcart
     *
     * @param $row
     *
     * @return $this
     */
    public function removeItem($row)
    {
        $content = $this->getContent();

        // the cart contains this row - so remove it
        if ($content->has($row)) {
            $content->pull($row);
            $this->content = serialize($content);

            $this->save();
        }

        return $this;
    }

    /**
     * Removes the cart from the database
     */
    public function clear()
    {
        $this->delete();
    }

    /**
     * Update the cart item with the given rowId.
     *
     * @param string $row
     * @param mixed  $qty
     * @param array  $options
     *
     * @return ShoppingCart
     */
    public function updateItem($row, $qty = 1, array $options = [])
    {
        $cartItem = $this->getRow($row);

        $cartItem->updateItem($qty, $options);

        $content = $this->getContent();

        $content->put($cartItem->rowId, $cartItem);

        $this->content = serialize($content);

        $this->save();

        return $this;
    }

    /**
     * @return \Illuminate\Support\Collection
     */
    public function getContent()
    {
        if (null === $this->content) {
            return collect();
        }

        return unserialize($this->content);
    }

    /**
     * @return int
     */
    public function getItemCount()
    {
        return $this->getContent()->count();
    }

    /**
     * Get the total price of the items in the cart.
     *
     * @return Money
     */
    public function getTotal()
    {
        $content = $this->getContent();

        $total = $content->reduce(function (Money $total, CartItem $cartItem) {
            return $total->add($cartItem->getTotal());
        }, new Money(0, Config::get('shoppingcart.currency')));

        return $total;
    }

    /**
     * Get the total tax of the items in the cart.
     *
     * @return Money
     */
    public function getTax()
    {
        $content = $this->getContent();

        $tax = $content->reduce(function (Money $tax, CartItem $cartItem) {
            return $tax->add($cartItem->getTaxTotal());
        }, new Money(0, Config::get('shoppingcart.currency')));

        return $tax;
    }

    /**
     * Get the subtotal (total - tax) of the items in the cart.
     *
     * @return Money
     */
    public function getSubTotal()
    {
        $content = $this->getContent();

        $subTotal = $content->reduce(function (Money $subTotal, CartItem $cartItem) {
            return $subTotal->add($cartItem->getSubtotal());
        }, new Money(0, Config::get('shoppingcart.currency')));

        return $subTotal;
    }

    /**
     * Format a money string
     *
     * @param Money $value
     *
     * @return string
     */
    public function formatMoney(Money $value)
    {
        $currencies = new ISOCurrencies();
        $moneyFormatter = new DecimalMoneyFormatter($currencies);

        return $moneyFormatter->format($value);
    }

    /**
     * @param $identifier
     * @param $name
     *
     * @return array
     */
    private function defaultValues($identifier, $name) {
        return [
            'identifier' => $identifier,
            'name' => $name,
        ];
    }

    /**
     * Get a cart item from the cart by its rowId.
     *
     * @param string $row
     *
     * @return CartItem
     * @throws InvalidShoppingCartRowException
     */
    private function getRow($row)
    {
        $content = $this->getContent();

        if (! $content->has($row)) {
            throw new InvalidShoppingCartRowException();
        }

        return $content->get($row);
    }
}

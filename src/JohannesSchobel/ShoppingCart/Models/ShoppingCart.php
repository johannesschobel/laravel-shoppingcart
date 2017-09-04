<?php

namespace JohannesSchobel\ShoppingCart\Models;

use Illuminate\Database\Eloquent\Model;
use JohannesSchobel\ShoppingCart\Contracts\Buyable;
use JohannesSchobel\ShoppingCart\Exceptions\InvalidShoppingCartRowException;

class ShoppingCart extends Model
{
    // todo fix this
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

    public function load($identifier, $name = null)
    {
        $name = $name ?: self::DEFAUlT_NAME;

        $classname = config('shoppingcart.models.shoppingcart');
        $shoppingcart = $classname::firstOrNew($this->defaultValues($identifier, $name));

        return $shoppingcart;
    }

    /**
     * Add an item to the cart.
     *
     * @param mixed     $id
     * @param mixed     $name
     * @param int|float $qty
     * @param float     $price
     * @param array     $options
     *
     * @return \JohannesSchobel\ShoppingCart\Models\ShoppingCart
     */
    public function addItem($id, $name = null, $qty = null, $price = null, array $options = [])
    {
        if ($this->isMulti($id)) {
            return array_map(function ($item) {
                return $this->addItem($item);
            }, $id);
        }

        $cartItem = $this->createCartItem($id, $name, $qty, $price, $options);

        $content = $this->getContent();

        if ($content->has($cartItem->rowId)) {
            $cartItem->qty += $content->get($cartItem->rowId)->qty;
        }

        $content->put($cartItem->rowId, $cartItem);

        $this->content = serialize($content);

        $this->save();

        return $this;
    }

    public function removeItem($row)
    {
        $content = $this->getContent();

        // the cart contains this row - so remove it
        if ($content->has($row)) {
            $content->pull($row);
            $this->content = serialize($content);
        }

        return $this;
    }

    public function clear()
    {
        $this->delete();
    }

    /**
     * Create a new CartItem from the supplied attributes.
     *
     * @param mixed     $id
     * @param mixed     $name
     * @param int|float $qty
     * @param float     $price
     * @param array     $options
     * @return \JohannesSchobel\ShoppingCart\Models\CartItem
     */
    private function createCartItem($id, $name, $qty, $price, array $options)
    {
        if ($id instanceof Buyable) {
            $cartItem = CartItem::fromBuyable($id, $options ?: []);
            $cartItem->setQuantity($qty ?: 1);
            $cartItem->associate($id);
        } elseif (is_array($id)) {
            $cartItem = CartItem::fromArray($id);
            $cartItem->setQuantity($id['qty']);
        } else {
            $cartItem = CartItem::fromAttributes($id, $name, $price, $options);
            $cartItem->setQuantity($qty);
        }

        $cartItem->setTaxRate(config('shoppingcart.tax'));

        return $cartItem;
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

    public function getContent()
    {
        if(null === $this->content) {
            return collect();
        }

        return unserialize($this->content);
    }

    /**
     * @return mixed
     */
    public function getItemCount() {
        return $this->getContent()->count();
    }

    /**
     * Get the total price of the items in the cart.
     *
     * @param int    $decimals
     *
     * @return float
     */
    public function getTotal($decimals = null)
    {
        $content = $this->getContent();

        $total = $content->reduce(function ($total, CartItem $cartItem) {
            return $total + ($cartItem->getTotal());
        }, 0);

        return $this->numberFormat($total, $decimals);
    }

    /**
     * Get the total tax of the items in the cart.
     *
     * @param int    $decimals
     *
     * @return float
     */
    public function getTax($decimals = null)
    {
        $content = $this->getContent();

        $tax = $content->reduce(function ($tax, CartItem $cartItem) use ($decimals){
            return $tax + ($cartItem->getTaxTotal($decimals));
        }, 0);

        return $this->numberFormat($tax, $decimals);
    }

    /**
     * Get the subtotal (total - tax) of the items in the cart.
     *
     * @param int    $decimals
     *
     * @return float
     */
    public function getSubTotal($decimals = null)
    {
        $content = $this->getContent();

        $subTotal = $content->reduce(function ($subTotal, CartItem $cartItem) {
            return $subTotal + ($cartItem->getSubtotal());
        }, 0);

        return $this->numberFormat($subTotal, $decimals);
    }

    /**
     * Get the formatted number
     *
     * @param $value
     * @param $decimals
     *
     * @return int|float
     */
    private function numberFormat($value, $decimals = null)
    {
        if (is_null($decimals)) {
            $decimals = config('shoppingcart.format.decimals', 2);
        }

        return round(floatval($value), $decimals);
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
     * Check if the item is a multidimensional array or an array of Buyables.
     *
     * @param mixed $item
     * @return bool
     */
    private function isMulti($item)
    {
        if ( ! is_array($item)) return false;
        return is_array(head($item)) || head($item) instanceof Buyable;
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

        if ( ! $content->has($row))
            throw new InvalidShoppingCartRowException();

        return $content->get($row);
    }
}

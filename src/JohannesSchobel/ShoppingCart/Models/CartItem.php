<?php

namespace JohannesSchobel\ShoppingCart\Models;

use Illuminate\Contracts\Support\Arrayable;
use JohannesSchobel\ShoppingCart\Contracts\Buyable;

class CartItem implements Arrayable
{
    /**
     * The rowID of the cart item.
     *
     * @var string
     */
    public $rowId;

    /**
     * The ID of the cart item.
     *
     * @var int|string
     */
    public $id;

    /**
     * The quantity for this cart item.
     *
     * @var int|float
     */
    public $qty;

    /**
     * The name of the cart item.
     *
     * @var string
     */
    public $name;

    /**
     * The price without TAX of the cart item.
     *
     * @var float
     */
    public $price;

    /**
     * The options for this cart item.
     *
     * @var array
     */
    public $options;

    /**
     * The FQN of the associated model.
     *
     * @var string|null
     */
    private $model = null;

    /**
     * The tax rate for the cart item.
     *
     * @var int|float
     */
    private $taxRate = 0;

    /**
     * CartItem constructor.
     *
     * @param int|string $id
     * @param string     $name
     * @param float      $price
     * @param array      $options
     *
     * @throws \InvalidArgumentException
     */
    public function __construct($id, $name, $price, array $options = [])
    {
        if (empty($id)) {
            throw new \InvalidArgumentException('Please supply a valid identifier.');
        }
        if (empty($name)) {
            throw new \InvalidArgumentException('Please supply a valid name.');
        }
        if (strlen($price) < 0 || ! is_numeric($price)) {
            throw new \InvalidArgumentException('Please supply a valid price.');
        }

        $this->id       = $id;
        $this->name     = $name;
        $this->price    = floatval($price);
        $this->options  = new CartItemOptions($options);
        $this->rowId    = $this->generateRowId($id, $options);
    }

    /**
     * Returns the formatted price without TAX.
     *
     * @param int    $decimals
     *
     * @return float
     */
    public function price($decimals = null)
    {
        return $this->numberFormat($this->price, $decimals);
    }

    /**
     * Returns the formatted price with TAX.
     *
     * @param int $decimals
     *
     * @return float
     *
     */
    public function getPriceTax($decimals = null)
    {
        $value = $this->price + $this->getTax();
        return $this->numberFormat($value, $decimals);
    }

    /**
     * Returns the formatted subtotal.
     * Subtotal is price for whole CartItem without TAX
     *
     * @param int    $decimals
     *
     * @return float
     */
    public function getSubtotal($decimals = null)
    {
        $value = $this->qty * $this->price;
        return $this->numberFormat($value, $decimals);
    }
    
    /**
     * Returns the formatted total.
     * Total is price for whole CartItem with TAX
     *
     * @param int    $decimals
     *
     * @return float
     */
    public function getTotal($decimals = null)
    {
        $value = $this->qty * ($this->getPriceTax());
        return $this->numberFormat($value, $decimals);
    }

    /**
     * Returns the formatted tax.
     *
     * @param int    $decimals
     *
     * @return float
     */
    public function getTax($decimals = null)
    {
        $value = $this->price * ($this->taxRate / 100);
        return $this->numberFormat($value, $decimals);
    }
    
    /**
     * Returns the formatted tax.
     *
     * @param int    $decimals
     *
     * @return float
     */
    public function getTaxTotal($decimals = null)
    {
        $value = $this->getTax() * $this->qty;
        return $this->numberFormat($value, $decimals);
    }

    /**
     * Set the quantity for this cart item.
     *
     * @param int|float $qty
     *
     * @throws \InvalidArgumentException
     */
    public function setQuantity($qty)
    {
        if (empty($qty) || ! is_numeric($qty))
            throw new \InvalidArgumentException('Please supply a valid quantity.');

        $this->qty = $qty;
    }

    /**
     * @param       $qty
     * @param array $options
     */
    public function updateItem($qty, $options = [])
    {
        $this->qty = $qty;
        $this->options = $options;
    }

    /**
     * Associate the cart item with the given model.
     *
     * @param mixed $model
     * @return \JohannesSchobel\ShoppingCart\Models\CartItem
     */
    public function associate($model)
    {
        $this->model = is_string($model) ? $model : get_class($model);
        
        return $this;
    }

    /**
     * Set the tax rate.
     *
     * @param int|float $taxRate
     * @return \JohannesSchobel\ShoppingCart\Models\CartItem
     */
    public function setTaxRate($taxRate)
    {
        $this->taxRate = $taxRate;
        
        return $this;
    }

    /**
     * Get an attribute from the cart item or get the associated model.
     *
     * @param string $attribute
     * @return mixed
     */
    public function __get($attribute)
    {
        if (property_exists($this, $attribute)) {
            return $this->{$attribute};
        }

        if ($attribute === 'model') {
            return with(new $this->model)->find($this->id);
        }

        return null;
    }

    /**
     * Create a new instance from a Buyable.
     *
     * @param \JohannesSchobel\ShoppingCart\Contracts\Buyable $item
     * @param array   $options
     *
     * @return \JohannesSchobel\ShoppingCart\Models\\CartItem
     */
    public static function fromBuyable(Buyable $item, array $options = [])
    {
        return new self($item->getBuyableIdentifier($options), $item->getBuyableDescription($options), $item->getBuyablePrice($options), $options);
    }

    /**
     * Create a new instance from the given array.
     *
     * @param array $attributes
     *
     * @return \JohannesSchobel\ShoppingCart\Models\CartItem
     */
    public static function fromArray(array $attributes)
    {
        $options = array_get($attributes, 'options', []);

        return new self($attributes['id'], $attributes['name'], $attributes['price'], $options);
    }

    /**
     * Create a new instance from the given attributes.
     *
     * @param int|string $id
     * @param string     $name
     * @param float      $price
     * @param array      $options
     *
     * @return \JohannesSchobel\ShoppingCart\Models\\CartItem
     */
    public static function fromAttributes($id, $name, $price, array $options = [])
    {
        return new self($id, $name, $price, $options);
    }

    /**
     * Generate a unique id for the cart item.
     *
     * @param string $id
     * @param array  $options
     * @return string
     */
    protected function generateRowId($id, array $options)
    {
        ksort($options);

        return md5($id . serialize($options));
    }

    /**
     * Get the instance as an array.
     *
     * @return array
     */
    public function toArray()
    {
        return [
            'rowId'    => $this->rowId,
            'id'       => $this->id,
            'name'     => $this->name,
            'qty'      => $this->qty,
            'values'   => [
                'price'    => $this->price(),
                'subtotal' => $this->getSubtotal(),
                'tax'      => $this->getTax(),
                'taxrate'  => $this->taxRate,
                'taxtotal' => $this->getTaxTotal(),
                'total'    => $this->getTotal(),
            ],

            'options'  => $this->options,
        ];
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
}

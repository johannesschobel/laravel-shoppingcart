<?php

namespace JohannesSchobel\ShoppingCart\Models;

use Illuminate\Contracts\Support\Arrayable;
use JohannesSchobel\ShoppingCart\Contracts\Buyable;
use Money\Currencies\ISOCurrencies;
use Money\Formatter\DecimalMoneyFormatter;
use Money\Money;

class CartItem implements Arrayable
{
    /**
     * The rowID of the cart item.
     *
     * @var string
     */
    private $rowId;

    /**
     * The ID of the cart item.
     *
     * @var int|string
     */
    private $id;

    /**
     * The quantity for this cart item.
     *
     * @var int
     */
    private $qty = 1;

    /**
     * The name of the cart item.
     *
     * @var string
     */
    private $name;

    /**
     * The money object representing the price WITHOUT taxes
     *
     * @var null
     */
    private $price = null;

    /**
     * The options for this cart item.
     *
     * @var array
     */
    private $options;

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
     * @param Money      $price
     * @param array      $options
     *
     * @throws \InvalidArgumentException
     */
    public function __construct($id, $name, Money $price, array $options = [])
    {
        if (empty($id)) {
            throw new \InvalidArgumentException('Please supply a valid identifier.');
        }

        if (empty($name)) {
            throw new \InvalidArgumentException('Please supply a valid name.');
        }

        if ($price->isNegative()) {
            throw new \InvalidArgumentException('Please supply a valid price.');
        }

        $this->id       = $id;
        $this->name     = $name;
        $this->price    = $price;
        $this->options  = new CartItemOptions($options);
        $this->rowId    = $this->generateRowId($id, $options);
    }

    /**
     * Returns the price without TAX.
     *
     * @return Money
     */
    public function getPrice()
    {
        return $this->price;
    }

    /**
     * Returns the price with tax.
     *
     * @return Money
     */
    public function getPriceWithTax()
    {
        $value = $this->price->add($this->getTax());
        return $value;
    }

    /**
     * Returns the subtotal (price for whole CartItem without TAX)
     *
     * @return Money
     */
    public function getSubtotal()
    {
        $value = $this->price->multiply($this->qty);
        return $value;
    }
    
    /**
     * Returns the total price for whole CartItem with TAX
     *
     * @return Money
     */
    public function getTotal()
    {
        $value = $this->getPriceWithTax()->multiply($this->qty);
        return $value;
    }

    /**
     * Returns the tax for one single item.
     *
     * @return Money
     */
    public function getTax()
    {
        $rate = $this->taxRate / 100;
        $value = $this->price->multiply($rate);
        return $value;
    }
    
    /**
     * Returns the total taxes.
     *
     * @return Money
     */
    public function getTaxTotal()
    {
        $value = $this->getTax()->multiply($this->qty);
        return $value;
    }

    /**
     * Set the quantity for this cart item.
     *
     * @param int $qty
     *
     * @throws \InvalidArgumentException
     */
    public function setQuantity($qty)
    {
        if (empty($qty) || ! is_numeric($qty)) {
            throw new \InvalidArgumentException('Please supply a valid quantity.');
        }

        $this->qty = $qty;
    }

    /**
     * @return int
     */
    public function getQuantity()
    {
        return $this->qty;
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
     * @param int $taxRate
     *
     * @return CartItem
     * @throws \InvalidArgumentException
     */
    public function setTaxRate($taxRate)
    {
        if (empty($taxRate) || ! is_numeric($taxRate)) {
            throw new \InvalidArgumentException('Please supply a valid tax rate.');
        }

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
     * @return \JohannesSchobel\ShoppingCart\Models\CartItem
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
     * @param Money      $value
     * @param array      $options
     *
     * @return \JohannesSchobel\ShoppingCart\Models\CartItem
     */
    public static function fromAttributes($id, $name, $value, array $options = [])
    {
        return new self($id, $name, $value, $options);
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
            'value'   => [
                'currency' =>  $this->price->getCurrency(),

                'price'    => $this->formatMoney($this->getPrice()),
                'subtotal' => $this->formatMoney($this->getSubtotal()),

                'taxes'    => [
                    'tax' => $this->formatMoney($this->getTax()),
                    'rate' => (string)$this->taxRate,
                    'total' => $this->formatMoney($this->getTaxTotal()),
                ],

                'total'    => $this->formatMoney($this->getTotal()),
            ],

            'options'  => $this->options,
        ];
    }

    /**
     * Format a money string
     *
     * @param Money $value
     *
     * @return string
     */
    private function formatMoney(Money $value)
    {
        $currencies = new ISOCurrencies();
        $moneyFormatter = new DecimalMoneyFormatter($currencies);

        return $moneyFormatter->format($value);
    }
}

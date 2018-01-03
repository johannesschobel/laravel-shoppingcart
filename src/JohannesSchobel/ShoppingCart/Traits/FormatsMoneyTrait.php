<?php

namespace JohannesSchobel\ShoppingCart\Traits;

use Money\Currencies\ISOCurrencies;
use Money\Formatter\DecimalMoneyFormatter;
use Money\Money;

trait FormatsMoneyTrait
{
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
     * @param Money $value
     *
     * @return array
     */
    public function formatMoneyAsArray(Money $value)
    {
        return [
            'amount' => $this->formatMoney($value),
            'currency' => $value->getCurrency(),
        ];
    }

    /**
     * @param Money $value
     * @param bool  $appendCurrency Whether the currency (e.g., EUR) should be appended or prepended
     *
     * @return string
     */
    public function formatMoneyAsSimpleString(Money $value, $appendCurrency = true)
    {
        $str = $this->formatMoney($value);
        if ($appendCurrency) {
            $str = $str . ' ' . $value->getCurrency();
        }
        else {
            $str = $value . ' ' . $str;
        }

        return $str;
    }
}
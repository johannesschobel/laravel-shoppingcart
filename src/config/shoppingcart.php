<?php

return [

    'models' => [

        /*
        |--------------------------------------------------------------------------
        | ShoppingCart Model
        |--------------------------------------------------------------------------
        |
        | The model of your ShoppingCart. The model must extend
        | JohannesSchobel\ShoppingCart\Models\ShoppingCart
        |
        */

        'shoppingcart' => JohannesSchobel\ShoppingCart\Models\ShoppingCart::class,

    ],

    /*
    |--------------------------------------------------------------------------
    | Default tax rate
    |--------------------------------------------------------------------------
    |
    | This default tax rate will be used
    |
    */
    'tax' => 19,

    /*
    |--------------------------------------------------------------------------
    | Default currency
    |--------------------------------------------------------------------------
    |
    | This default currency will be used if nothing is set
    |
    */
    'currency' => new \Money\Currency('EUR'),


];
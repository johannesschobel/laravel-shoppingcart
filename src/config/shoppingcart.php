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
    | Shoppingcart database settings
    |--------------------------------------------------------------------------
    |
    | Here you can set the connection that the shoppingcart should use when
    | storing and restoring a cart.
    |
    */

    'database' => [

        'connection' => null,

        'table' => 'shoppingcarts',

    ],

    /*
    |--------------------------------------------------------------------------
    | Default number format
    |--------------------------------------------------------------------------
    |
    | This defaults will be used for the formatted numbers if you don't
    | set them in the method call.
    |
    */

    'format' => [

        'decimals' => 2,

        'decimal_point' => '.',

        'thousand_separator' => ','

    ],

];
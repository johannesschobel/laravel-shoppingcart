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
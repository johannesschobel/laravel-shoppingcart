<?php

namespace JohannesSchobel\ShoppingCart\Exceptions;

use Exception;

class InvalidShoppingCartRowException extends Exception
{
    public $message = 'The Shopping Cart does not contain this Row';
}
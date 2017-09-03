<?php

namespace JohannesSchobel\ShoppingCart\Exceptions;

use \Exception;

class ShoppingCartNotFoundException extends Exception
{
    public $message = 'The requested Shopping Cart does not exist.';
}
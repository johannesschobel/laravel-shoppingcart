# laravel-shoppingcart
Provides an invitation mechanism for Laravel applications. Note that this package **does not** handle, how the 
invitation is sent to the user (e.g., via email).

# Installation
First, add the respective line to your composer file
``` json
"require" : {
   ... ,
   "johannesschobel/laravel-shoppingcart": "0.*" ,
}
```

and run `composer install` to install the new component.

Then add respective `ServiceProvider` from the package to your `config/app.php` configuration file, like this:

``` php
'providers' => [
   ... ,
   JohannesSchobel\ShoppingCart\ShoppingCartServiceProvider::class,
],
```

and register the Facade
``` php
'aliases' => [
   ... ,
   'ShoppingCart' => JohannesSchobel\ShoppingCart\Facades\ShoppingCart::class,
],
```

Then, you simply add the provided `migration` file using the following command:
```php
php artisan vendor:publish --provider="JohannesSchobel\ShoppingCart\ShoppingCartServiceProvider" --tag="migrations"
```
and then you migrate your database using
```php
php artisan db:migrate
```

If you want, you can overwrite the basic configuration of this package using the following command:

```php
php artisan vendor:publish --provider="JohannesSchobel\ShoppingCart\ShoppingCartServiceProvider" --tag="config"
```

This will copy the `shoppingcart` configuration file to your `config` folder. Using this file, you can 
customize the various parameters of the package. Currently, not many are available, however, I will be adding more 
and more ;)

# Usage
to be done
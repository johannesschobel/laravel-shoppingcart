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

The `ShoppingCart` Facade provides some neat methods to deal with the shopping cart in general. These methods are:

## LOAD a ShoppingCart from the database

```php
ShoppingCart::load($identifier, $name = null);
```

Load the cart with the `identifier` and `name` from the database. If no `name` is provided, the default name `default` 
will be used. If no cart exists, an empty cart will be returned. This cart remains temporary as long as no items are 
stored.

## CLEAR a ShoppingCart

```php
ShoppingCart::clear();
```

Removes the current instance of the cart from the database.

## ADD Items to the Cart

```php
ShoppingCart::addItem(
   $id, 
   $name = null, 
   $type = null, 
   $qty = 1, 
   Money $price = null, 
   $uri = null, 
   array $options = []
);
```

This method allows for adding items to the cart. The basic usage allows you to directly specify the item you want
to set. For example
```php
ShoppingCart::addItem(
   '1234', 
   'Basic T-Shirt', 
   'products', 
   10, 
   new Money(999, new Currency('EUR')), // note the value is added in cents! 
   '/products/1234', 
   ['size' => 'large', 'color' => 'black']
);
```
would add 10 "Basic T-Shirts", each costs 9.99 EUR to the cart. The customer has specified a color and size.

You may, however, add the `Buyable` interface to your products in order to simplify this process. This will require you 
to implement additional methods on the model (you can add the `CanBePurchased` Trait in order to make a "best guess").

This would allow you to just add a specific product:
```php
$product = Product::find(1234); // remember, Product must implement the Buyable interface!
ShoppingCart::addBuyable(
   $product, 
   10, 
   ['size' => 'large', 'color' => 'black']
);
```
would result in the same cart as above. However, the `id`, `name`, `price` and `uri` are directly taken from the model!

## REMOVE Items from the Cart

```php
ShoppingCart::removeItem($row)
```

To remove an item from the shopping cart you need to have its `rowId`. This `rowId` can be obtained, for example, via 
the `ShoppingCart::load()` or `ShoppingCart::getContent()` method.

```php
$rowId = "30168b5f5a78bc48d08b4d5a125a9d90";
ShoppingCart::removeItem($rowId);
```

## UPDATE Items in the Cart

```php
ShoppingCart::updateItem($row, $qty = 1, array $options = [])
```

allows you to update a given row in the cart. This `rowId` can be obtained, for example, via the 
`ShoppingCart::load()` or `ShoppingCart::getContent()` method.

```php
$rowId = "30168b5f5a78bc48d08b4d5a125a9d90";
ShoppingCart::updateItem($rowId, 1, ['color' => 'red']);
```
would update the quantity and options of the item (e.g., the product to be purchased shall be 'red' instead of 'black').

## Items / Price / Taxes

The Cart also provides methods to
* get the amount of items in the cart => `getItemCount()`
* get the content of the cart. This returns all items in the cart => `getContent()`
* get the value of the current cart
  * get the taxes of the cart => `getTaxes()`
  * get total (value including taxes) => `getTotal()`
  * get subtotal (value without taxes) => `getSubTotal()`
* get the "associated" `Buyable` model => `resolveModel()`
  

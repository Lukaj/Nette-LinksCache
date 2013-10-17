# LinksCache #
This addon for Nette Framework enables caching links. Link generating is by far the slowest part of Nette framework and with caching already generated links you can increase performance by tens of percent.

## Installing ##
Prefered method of getting LinksCache is [Composer](https://getcomposer.org/). The package is called _lukaj/nette-links-cache_. Alternatively you can download the source code directly from [Github](https://www.github.com).

## Adding to your project ##
### Registering extension ###
If using Nette 2.1 or newer add to your `config.neon` file following code
```php
extensions:
    LinksCache: Lukaj\LinksCache\DI\LinksCacheExtension
```
If using Nette 2.0 add to your `bootstrap.php` this function call
```php
Lukaj\LinksCache\DI\LinksCacheExtension::register($configurator);
// suppose that $configurator contains instance of Nette\Config\Configurator
```

### Adding to presenter ###
Add to your `BasePresenter`
```php
/** 
 * @var Lukaj\LinksCache\Cache
 * @inject
 */
public $linksCache;
```

There are two ways of using LinksCache. The first one let you decide which links you want to cache, the second one caches all the links. If you want selectively cache only few links just call `$this->linksCache->link($destination, $args)` instead of `$this->link($destination, $arguments)`.

If you want to cache all the links, method `PresenterComponent::Link($destination, $args)` should be overwritten. LinksCache porvides special methods for this. Your `BasePresenter` could look like this
```php
/**
 * @param string $destination
 * @param array $args
 * @return string
 */
public function link($destination, $args = array())
{
    if (($link = $this->linksCache->getFromCache($destination, $args)) === NULL) {
        $link = $this->linksCache->add($destination, $args, parent::link($destination, $args));
	}
	return $link;
}
```

If you dont want to write this code, and inject annotation, LinksCache provides a convenient trait called `Lukaj\LinksCache\LinksCacheTrait`. Just add to `BasePresenter`
```php
use Lukaj\LinksCache\LinksCache
```
and everything will be done automatically. This trait also contains methods `startLinksCache()` and `stopLinksCache()` which can turn on and off the caching.

## Configuration ##
LinksCache support configuration via `config.neon`. 

* expiration - cache expiration ins seconds, default value is 864000 (ten days)
* key - a key which is used for storing data in cache, default value is global

#### Using multiple keys####
Is is possible to use different keys for example for user or presenters.
```php
public function startup()
{
    $this->linksCache->setOptions(array('key' => $this->user->identity->id));
    // sets different key for every user
    
    $this->linksCache->setOptions(array('key' => $this->getName()));
    // sets different key for every presenter
}
```
## Author ##
© Copyright Lukas Mazur 2013. Licensed under LGPL v3.0. Please see the file LICENSE.md for more information.
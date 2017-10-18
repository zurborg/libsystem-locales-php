System\Locales
===============






* Class name: Locales
* Namespace: System



Constants
----------


### CATEGORIES

    const CATEGORIES = array('all', 'collate', 'ctype', 'monetary', 'numeric', 'time', 'messages')







Methods
-------


### postUpdate

    mixed System\Locales::postUpdate()





* Visibility: **public**
* This method is **static**.




### initialize

    mixed System\Locales::initialize()





* Visibility: **public**
* This method is **static**.




### normalize

    mixed System\Locales::normalize(\System\string $locale)





* Visibility: **public**
* This method is **static**.


#### Arguments
* $locale **System\string**



### lookup

    mixed System\Locales::lookup(\System\string $locale)





* Visibility: **public**
* This method is **static**.


#### Arguments
* $locale **System\string**



### search

    mixed System\Locales::search(\System\string $have, \System\string $query, \System\string $want)





* Visibility: **public**
* This method is **static**.


#### Arguments
* $have **System\string**
* $query **System\string**
* $want **System\string**



### get

    mixed System\Locales::get($category)





* Visibility: **public**
* This method is **static**.


#### Arguments
* $category **mixed**



### set

    mixed System\Locales::set($category, $value)





* Visibility: **public**
* This method is **static**.


#### Arguments
* $category **mixed**
* $value **mixed**



### wrap

    mixed System\Locales::wrap($locale, callable $function)





* Visibility: **public**
* This method is **static**.


#### Arguments
* $locale **mixed**
* $function **callable**



### iconv

    mixed System\Locales::iconv(\System\string $ctype, \System\string $in_charset, \System\string $out_charset, \System\string $str)





* Visibility: **public**
* This method is **static**.


#### Arguments
* $ctype **System\string**
* $in_charset **System\string**
* $out_charset **System\string**
* $str **System\string**



# URL query string manipulations helper

Adds/removes/updates query string of current request uri, accepts Laravel's array "dot" notation

## Installation

You can install the package via composer:

```bash
composer require vitnasinec/uri 
```

## Usage

``` php
// ...?filter[foo]=bar&sort=-baz

uri()->addQuery('filter.other', 'next');
// ...?filter[foo]=bar&filter[other]=next&sort=-baz

uri()->removeQuery('filter.foo')
// ...?sort=-baz

uri()->mergeQuery(['filter.foo' => 'changed', 'filter.other' => 'next']);
// ...?filter[foo]=changed&filter[other]=next&sort=-baz

```

### Testing

``` bash
composer test
```

### Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information what has changed recently.

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.

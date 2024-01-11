# URL query string manipulations helper

Adds/removes/updates query string of current request uri, accepts Laravel's array "dot" notation

## Installation

You can install the package via composer:

```bash
composer require vitnasinec/uri
```

## Usage

``` php
// https://domain.test/page?filter[foo]=bar&sort=-baz

uri()->addQuery('filter.other', 'next');
// https://domain.test/page?filter[foo]=bar&filter[other]=next&sort=-baz

uri('http://different.test/other?filter[other]=previous')->addQuery('filter.other', 'next');
// http://different.test/other?filter[other]=next

uri()->removeQuery('filter.foo')
// https://domain.test/page?sort=-baz

uri()->mergeQuery(['filter.foo' => 'changed', 'filter.other' => 'next']);
// https://domain.test/page?filter[foo]=changed&filter[other]=next&sort=-baz

uri()->mergeMissingQuery(['filter.foo' => 'skipped', 'filter.added' => true]);
// https://domain.test/page?filter[foo]=changed&filter[other]=next&sort=-baz

```

### Testing

``` bash
composer test
```

### Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information what has changed recently.

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.

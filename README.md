# Array View PHP
![Packagist PHP Version Support](https://img.shields.io/packagist/php-v/smoren/array-view)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/Smoren/array-view-php/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/Smoren/array-view-php/?branch=master)
[![Coverage Status](https://coveralls.io/repos/github/Smoren/array-view-php/badge.svg?branch=master)](https://coveralls.io/github/Smoren/array-view-php?branch=master)
![Build and test](https://github.com/Smoren/array-view-php/actions/workflows/test_master.yml/badge.svg)
[![License: MIT](https://img.shields.io/badge/License-MIT-yellow.svg)](https://opensource.org/licenses/MIT)

**Array View** is a PHP library that provides a powerful set of utilities for working with arrays in
a versatile and efficient manner. These classes enable developers to create views of arrays, manipulate data with ease,
and select specific elements using index lists, masks, and slice parameters.

Array View offers a Python-like slicing experience for efficient data manipulation and selection of array elements.

## Features
- Create array views for easy data manipulation.
- Select elements using [Python-like slice notation](https://www.geeksforgeeks.org/python-list-slicing/).
- Handle array slicing operations with ease.
- Enable efficient selection of elements using index lists and boolean masks.


## How to install to your project
```bash
composer require smoren/array-view
```

## Usage
## Quick examples
### Slicing
```php
use Smoren\ArrayView\Views\ArrayView;

$originalArray = [1, 2, 3, 4, 5, 6, 7, 8, 9];
$originalView = ArrayView::toView($originalArray);

$originalView['1:7:2']; // [2, 4, 6]
$originalView[':3']; // [1, 2, 3]
$originalView['::-1']; // [9, 8, 7, 6, 5, 4, 3, 2, 1]

$originalView[2]; // 3
$originalView[4]; // 5

$originalView['1:7:2'] = [22, 44, 66];
print_r($originalView); // [1, 22, 3, 44, 5, 66, 7, 8, 9]
```

### Subviews
```php
use Smoren\ArrayView\Selectors\IndexListSelector;
use Smoren\ArrayView\Selectors\MaskSelector;
use Smoren\ArrayView\Selectors\SliceSelector;
use Smoren\ArrayView\Views\ArrayView;

$originalArray = [1, 2, 3, 4, 5];
$originalView = ArrayView::toView($originalArray);

$originalView.subview(new MaskSelector([true, false, true, false, true])).toArray(); // [1, 3, 5]
$originalView.subview(new IndexListSelector([1, 2, 4])).toArray(); // [2, 3, 5]
$originalView.subview(new SliceSelector('::-1')).toArray(); // [5, 4, 3, 2, 1]
$originalView.subview('::-1').toArray(); // [5, 4, 3, 2, 1]

$originalView.subview(new MaskSelector([true, false, true, false, true])).apply(fn ($x) => x * 10);
print_r(originalArray); // [10, 2, 30, 4, 50]
```

### Subarrays
```php
use Smoren\ArrayView\Selectors\IndexListSelector;
use Smoren\ArrayView\Selectors\MaskSelector;
use Smoren\ArrayView\Selectors\SliceSelector;
use Smoren\ArrayView\Views\ArrayView;

$originalArray = [1, 2, 3, 4, 5];
$originalView = ArrayView::toView($originalArray);

$originalView[new MaskSelector([true, false, true, false, true])]; // [1, 3, 5]
$originalView[new IndexListSelector([1, 2, 4])]; // [2, 3, 5]
$originalView[new SliceSelector('::-1')]; // [5, 4, 3, 2, 1]
$originalView['::-1']; // [5, 4, 3, 2, 1]

$originalView[new MaskSelector([true, false, true, false, true])] = [10, 30, 50];
print_r(originalArray); // [10, 2, 30, 4, 50]
```

### Combining subviews
```php
use Smoren\ArrayView\Selectors\IndexListSelector;
use Smoren\ArrayView\Selectors\MaskSelector;
use Smoren\ArrayView\Selectors\SliceSelector;
use Smoren\ArrayView\Views\ArrayView;

const $originalArray = [1, 2, 3, 4, 5, 6, 7, 8, 9, 10];

const $subview = ArrayView::toView(originalArray)
    ->subview('::2')                                             // [1, 3, 5, 7, 9]
    ->subview(new MaskSelector([true, false, true, true, true])) // [1, 5, 7, 9]
    ->subview(new IndexListSelector([0, 1, 2]))                  // [1, 5, 7]
    ->subview('1:');                                             // [5, 7]

$subview[':'] = [55, 77];
print_r($originalArray); // [1, 2, 3, 4, 55, 6, 77, 8, 9, 10]
```

## Unit testing
```
composer install
composer test-init
composer test
```

## Contributing
Contributions are welcome! Feel free to open an issue or submit a pull request on the [GitHub repository](https://github.com/Smoren/array-view-ts).

## Standards
ArrayView conforms to the following standards:

* PSR-1 — [Basic coding standard](https://www.php-fig.org/psr/psr-1/)
* PSR-4 — [Autoloader](https://www.php-fig.org/psr/psr-4/)
* PSR-12 — [Extended coding style guide](https://www.php-fig.org/psr/psr-12/)

## License
ArrayView PHP is licensed under the MIT License.

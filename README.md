# Array View PHP
![Packagist PHP Version Support](https://img.shields.io/packagist/php-v/smoren/array-view)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/Smoren/array-view-php/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/Smoren/array-view-php/?branch=master)
[![Coverage Status](https://coveralls.io/repos/github/Smoren/array-view-php/badge.svg?branch=master)](https://coveralls.io/github/Smoren/array-view-php?branch=master)
![Build and test](https://github.com/Smoren/array-view-php/actions/workflows/test.yml/badge.svg)
[![License: MIT](https://img.shields.io/badge/License-MIT-yellow.svg)](https://opensource.org/licenses/MIT)

**Array View** is a PHP library that provides powerful abstractions and utilities for working with lists of data.
Create views of arrays, slice and index using Python-like notation, transform and select your data using chained and
fluent operations.

## Features
- Array views as an abstraction over an array
- Forward and backward array indexing
- Selecting and slicing using [Python-like slice notation](https://www.geeksforgeeks.org/python-list-slicing/)
- Filtering, mapping, matching and masking
- Chaining operations via pipes and fluent interfaces


## How to install to your project
```bash
composer require smoren/array-view
```

## Usage
### Indexing

Index into an array forward or backwards using positive or negative indexes.
```php
use Smoren\ArrayView\Views\ArrayView;

$view = ArrayView::toView([1, 2, 3, 4, 5, 6, 7, 8, 9]);

$view[0];  // 1
$view[1];  // 2
$view[-1]; // 9
$view[-2]; // 8
```

### Slices

Use [Python-like slice notation](https://www.geeksforgeeks.org/python-list-slicing/) to select a range of elements: `[start, stop, step]`.
```php
use Smoren\ArrayView\Views\ArrayView;

$originalArray = [1, 2, 3, 4, 5, 6, 7, 8, 9];
$view = ArrayView::toView($originalArray);

$view['1:6'];   // [2, 3, 4, 5, 6]
$view['1:7:2']; // [2, 4, 6]
$view[':3'];    // [1, 2, 3]
$view['::-1'];  // [9, 8, 7, 6, 5, 4, 3, 2, 1]
```

Insert into parts of the array.
```php
$view['1:7:2'] = [22, 44, 66];
print_r($originalArray); // [1, 22, 3, 44, 5, 66, 7, 8, 9]
```

### Subviews

Create subviews of the original view using masks, indexes, and slices.
```php
use Smoren\ArrayView\Selectors\IndexListSelector;
use Smoren\ArrayView\Selectors\MaskSelector;
use Smoren\ArrayView\Selectors\SliceSelector;
use Smoren\ArrayView\Views\ArrayView;

$originalArray = [1, 2, 3, 4, 5];
$view = ArrayView::toView($originalArray);

// Object-oriented style
$view->subview(new MaskSelector([true, false, true, false, true]))->toArray(); // [1, 3, 5]
$view->subview(new IndexListSelector([1, 2, 4]))->toArray();                   // [2, 3, 5]
$view->subview(new SliceSelector('::-1'))->toArray();                          // [5, 4, 3, 2, 1]

// Scripting style 
$view->subview([true, false, true, false, true])->toArray(); // [1, 3, 5]
$view->subview([1, 2, 4])->toArray();                        // [2, 3, 5]
$view->subview('::-1')->toArray();                           // [5, 4, 3, 2, 1]

$view->subview(new MaskSelector([true, false, true, false, true]))->apply(fn ($x) => x * 10);
print_r($originalArray); // [10, 2, 30, 4, 50]
```

### Subarray Multi-indexing

Directly select multiple elements using an array-index multi-selection.
```php
use Smoren\ArrayView\Selectors\IndexListSelector;
use Smoren\ArrayView\Selectors\MaskSelector;
use Smoren\ArrayView\Selectors\SliceSelector;
use Smoren\ArrayView\Views\ArrayView;

$originalArray = [1, 2, 3, 4, 5];
$view = ArrayView::toView($originalArray);

// Object-oriented style
$view[new MaskSelector([true, false, true, false, true])]; // [1, 3, 5]
$view[new IndexListSelector([1, 2, 4])];                   // [2, 3, 5]
$view[new SliceSelector('::-1')];                          // [5, 4, 3, 2, 1]

// Scripting style
$view[[true, false, true, false, true]]; // [1, 3, 5]
$view[[1, 2, 4]];                        // [2, 3, 5]
$view['::-1'];                           // [5, 4, 3, 2, 1]

$view[new MaskSelector([true, false, true, false, true])] = [10, 30, 50];
print_r($originalArray); // [10, 2, 30, 4, 50]
```

### Combining Subviews

Combine and chain subviews one after another in a fluent interface to perform multiple selection operations.
```php
use Smoren\ArrayView\Selectors\IndexListSelector;
use Smoren\ArrayView\Selectors\MaskSelector;
use Smoren\ArrayView\Selectors\SliceSelector;
use Smoren\ArrayView\Views\ArrayView;

$originalArray = [1, 2, 3, 4, 5, 6, 7, 8, 9, 10];

// Fluent object-oriented style
$subview = ArrayView::toView($originalArray)
    ->subview(new SliceSelector('::2'))                          // [1, 3, 5, 7, 9]
    ->subview(new MaskSelector([true, false, true, true, true])) // [1, 5, 7, 9]
    ->subview(new IndexListSelector([0, 1, 2]))                  // [1, 5, 7]
    ->subview(new SliceSelector('1:'));                          // [5, 7]

$subview[':'] = [55, 77];
print_r($originalArray); // [1, 2, 3, 4, 55, 6, 77, 8, 9, 10]

// Fluent scripting style
$originalArray = [1, 2, 3, 4, 5, 6, 7, 8, 9, 10];
$subview = ArrayView::toView($originalArray)
    ->subview('::2')                           // [1, 3, 5, 7, 9]
    ->subview([true, false, true, true, true]) // [1, 5, 7, 9]
    ->subview([0, 1, 2])                       // [1, 5, 7]
    ->subview('1:');                           // [5, 7]

$subview[':'] = [55, 77];
print_r($originalArray); // [1, 2, 3, 4, 55, 6, 77, 8, 9, 10]
```

### Selectors Pipe

Create pipelines of selections that can be saved and applied again and again to new array views.
```php
use Smoren\ArrayView\Selectors\IndexListSelector;
use Smoren\ArrayView\Selectors\MaskSelector;
use Smoren\ArrayView\Selectors\SliceSelector;
use Smoren\ArrayView\Views\ArrayView;

$originalArray = [1, 2, 3, 4, 5, 6, 7, 8, 9, 10];
$selector = new PipeSelector([
    new SliceSelector('::2'),
    new MaskSelector([true, false, true, true, true]),
    new IndexListSelector([0, 1, 2]),
    new SliceSelector('1:'),
]);

$view = ArrayView::toView($originalArray);
$subview = $view->subview($selector);
print_r($subview[':']); // [5, 7]

$subview[':'] = [55, 77];
print_r($originalArray); // [1, 2, 3, 4, 55, 6, 77, 8, 9, 10]
```

## Documentation
For detailed documentation and usage examples, please refer to the
[API documentation](https://smoren.github.io/array-view-php/packages/Application.html).

## Unit testing
```
composer install
composer test-init
composer test
```

## Contributing
Contributions are welcome! Feel free to open an issue or submit a pull request on the [GitHub repository](https://github.com/Smoren/array-view-php).

## Standards
ArrayView conforms to the following standards:

* PSR-1 — [Basic coding standard](https://www.php-fig.org/psr/psr-1/)
* PSR-4 — [Autoloader](https://www.php-fig.org/psr/psr-4/)
* PSR-12 — [Extended coding style guide](https://www.php-fig.org/psr/psr-12/)

## License
ArrayView PHP is licensed under the MIT License.

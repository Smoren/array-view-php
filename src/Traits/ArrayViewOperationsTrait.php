<?php

declare(strict_types=1);

namespace Smoren\ArrayView\Traits;

use Smoren\ArrayView\Exceptions\SizeError;
use Smoren\ArrayView\Exceptions\ValueError;
use Smoren\ArrayView\Interfaces\ArraySelectorInterface;
use Smoren\ArrayView\Interfaces\ArrayViewInterface;
use Smoren\ArrayView\Interfaces\MaskSelectorInterface;
use Smoren\ArrayView\Selectors\MaskSelector;
use Smoren\ArrayView\Util;
use Smoren\ArrayView\Views\ArrayMaskView;
use Smoren\ArrayView\Views\ArrayView;

/**
 * Trait providing methods for operation methods of ArrayView.
 *
 * @template T Type of ArrayView values.
 * @template S of string|array<int|bool>|ArrayViewInterface<int|bool>|ArraySelectorInterface Selector type.
 */
trait ArrayViewOperationsTrait
{
    /**
     * Filters the elements in the view based on a predicate function.
     *
     * ##### Example
     * ```php
     * $source = [1, 2, 3, 4, 5, 6];
     * $view = ArrayView::toView($source);
     *
     * $filtered = $view->filter(fn ($x) => $x % 2 === 0);
     * $filtered->toArray(); // [2, 4, 6]
     *
     * $filtered[':'] = [20, 40, 60];
     * $filtered->toArray(); // [20, 40, 60]
     * $source; // [1, 20, 3, 40, 5, 60]
     * ```
     *
     * @param callable(T, int): bool $predicate Function that returns a boolean value for each element.
     *
     * @return ArrayMaskView<T> A new view with elements that satisfy the predicate.
     */
    public function filter(callable $predicate): ArrayViewInterface
    {
        return $this->is($predicate)->select($this);
    }

    /**
     * Checks if all elements in the view satisfy a given predicate function.
     *
     * ##### Example
     * ```php
     * $source = [1, 2, 3, 4, 5, 6];
     * $view = ArrayView::toView($source);
     *
     * $mask = $view->is(fn ($x) => $x % 2 === 0);
     * $mask->getValue(); // [false, true, false, true, false, true]
     *
     * $view->subview($mask)->toArray(); // [2, 4, 6]
     * $view[$mask]; // [2, 4, 6]
     *
     * $view[$mask] = [20, 40, 60];
     * $source; // [1, 20, 3, 40, 5, 60]
     * ```
     *
     * @param callable(T, int): bool $predicate Function that returns a boolean value for each element.
     *
     * @return MaskSelector Boolean mask for selecting elements that satisfy the predicate.
     *
     * @see ArrayViewInterface::match() Full synonim.
     */
    public function is(callable $predicate): MaskSelectorInterface
    {
        $data = $this->toArray();
        return new MaskSelector(array_map($predicate, $data, array_keys($data)));
    }

    /**
     * Checks if all elements in the view satisfy a given predicate function.
     *
     * ##### Example
     * ```php
     * $source = [1, 2, 3, 4, 5, 6];
     * $view = ArrayView::toView($source);
     *
     * $mask = $view->match(fn ($x) => $x % 2 === 0);
     * $mask->getValue(); // [false, true, false, true, false, true]
     *
     * $view->subview($mask)->toArray(); // [2, 4, 6]
     * $view[$mask]; // [2, 4, 6]
     *
     * $view[$mask] = [20, 40, 60];
     * $source; // [1, 20, 3, 40, 5, 60]
     * ```
     *
     * @param callable(T, int): bool $predicate Function that returns a boolean value for each element.
     *
     * @return MaskSelector Boolean mask for selecting elements that satisfy the predicate.
     *
     * @see ArrayView::match() Full synonim.
     */
    public function match(callable $predicate): MaskSelectorInterface
    {
        return $this->is($predicate);
    }

    /**
     * Compares the elements of the current ArrayView instance with another array or ArrayView
     * using the provided comparator function.
     *
     * ##### Example
     *  ```php
     * $source = [1, 2, 3, 4, 5, 6];
     * $view = ArrayView::toView($source);
     *
     * $data = [6, 5, 4, 3, 2, 1];
     *
     * $mask = $view->matchWith($data, fn ($lhs, $rhs) => $lhs > $rhs);
     * $mask->getValue(); // [false, false, false, true, true, true]
     *
     * $view->subview($mask)->toArray(); // [4, 5, 6]
     * $view[$mask]; // [4, 5, 6]
     *
     * $view[$mask] = [40, 50, 60];
     * $source; // [1, 2, 3, 40, 50, 60]
     * ```
     *
     * @template U The type of the elements in the array for comparison with.
     *
     * @param array<U>|ArrayViewInterface<U>|U $data The array or ArrayView to compare to.
     * @param callable(T, U, int): bool $comparator Function that determines the comparison logic between the elements.
     *
     * @return MaskSelectorInterface A MaskSelector instance representing the results of the element comparisons.
     *
     * @throws ValueError if the $data is not sequential array.
     * @throws SizeError if size of $data not equals to size of the view.
     *
     * @see ArrayView::is() Full synonim.
     */
    public function matchWith($data, callable $comparator): MaskSelectorInterface
    {
        $data = $this->checkAndConvertArgument($data);
        return new MaskSelector(array_map($comparator, $this->toArray(), $data, array_keys($data)));
    }

    /**
     * Transforms each element of the array using the given callback function.
     *
     * The callback function receives two parameters: the current element of the array and its index.
     *
     * ##### Example
     *  ```php
     * $source = [1, 2, 3, 4, 5, 6, 7, 8, 9, 10];
     * $subview = ArrayView::toView($source)->subview('::2'); // [1, 3, 5, 7, 9]
     *
     * $subview->map(fn ($x) => $x * 10); // [10, 30, 50, 70, 90]
     * ```
     *
     * @param callable(T, int): T $mapper Function to transform each element.
     *
     * @return array<T> New array with transformed elements of this view.
     */
    public function map(callable $mapper): array
    {
        $result = [];
        $size = \count($this);
        for ($i = 0; $i < $size; $i++) {
            /** @var T $item */
            $item = $this[$i];
            $result[$i] = $mapper($item, $i);
        }
        return $result;
    }

    /**
     * Transforms each pair of elements from the current array view and the provided data array using the given
     * callback function.
     *
     * The callback function receives three parameters: the current element of the current array view,
     * the corresponding element of the data array, and the index.
     *
     * ##### Example
     * ```php
     * $source = [1, 2, 3, 4, 5, 6, 7, 8, 9, 10];
     * $subview = ArrayView::toView($source)->subview('::2'); // [1, 3, 5, 7, 9]
     *
     * $data = [9, 27, 45, 63, 81];
     *
     * $subview->mapWith($data, fn ($lhs, $rhs) => $lhs + $rhs); // [10, 30, 50, 70, 90]
     * ```
     *
     * @template U The type rhs of a binary operation.
     *
     * @param array<U>|ArrayViewInterface<U>|U $data The rhs values for a binary operation.
     * @param callable(T, U, int): T $mapper Function to transform each pair of elements.
     *
     * @return array<mixed> New array with transformed elements of this view.
     *
     * @throws ValueError if the $data is not sequential array.
     * @throws SizeError if size of $data not equals to size of the view.
     */
    public function mapWith($data, callable $mapper): array
    {
        $data = $this->checkAndConvertArgument($data);
        $result = [];

        $size = \count($this);
        for ($i = 0; $i < $size; $i++) {
            /** @var T $lhs */
            $lhs = $this[$i];
            /** @var U $rhs */
            $rhs = $data[$i];
            $result[$i] = $mapper($lhs, $rhs, $i);
        }

        return $result;
    }

    /**
     * Applies a transformation function to each element in the view.
     *
     * ##### Example
     * ```php
     * $source = [1, 2, 3, 4, 5, 6, 7, 8, 9, 10];
     * $subview = ArrayView::toView($source)->subview('::2'); // [1, 3, 5, 7, 9]
     *
     * $subview->apply(fn ($x) => $x * 10);
     *
     * $subview->toArray(); // [10, 30, 50, 70, 90]
     * $source; // [10, 2, 30, 4, 50, 6, 70, 8, 90, 10]
     * ```
     *
     * @param callable(T, int): T $mapper Function to transform each element.
     *
     * @return ArrayView<T> this view.
     */
    public function apply(callable $mapper): self
    {
        $size = \count($this);
        for ($i = 0; $i < $size; $i++) {
            /** @var T $item */
            $item = $this[$i];
            $this[$i] = $mapper($item, $i);
        }
        return $this;
    }

    /**
     * Sets new values for the elements in the view.
     *
     * ##### Example
     * ```php
     * $source = [1, 2, 3, 4, 5, 6, 7, 8, 9, 10];
     * $subview = ArrayView::toView($source)->subview('::2'); // [1, 3, 5, 7, 9]
     *
     * $data = [9, 27, 45, 63, 81];
     *
     * $subview->applyWith($data, fn ($lhs, $rhs) => $lhs + $rhs);
     * $subview->toArray(); // [10, 30, 50, 70, 90]
     *
     * $source; // [10, 2, 30, 4, 50, 6, 70, 8, 90, 10]
     * ```
     *
     * @template U Type of $data items.
     *
     * @param array<U>|ArrayViewInterface<U> $data
     * @param callable(T, U, int): T $mapper
     *
     * @return ArrayView<T> this view.
     *
     * @throws ValueError if the $data is not sequential array.
     * @throws SizeError if size of $data not equals to size of the view.
     */
    public function applyWith($data, callable $mapper): self
    {
        $data = $this->checkAndConvertArgument($data);

        $size = \count($this);
        for ($i = 0; $i < $size; $i++) {
            /** @var T $lhs */
            $lhs = $this[$i];
            /** @var U $rhs */
            $rhs = $data[$i];
            $this[$i] = $mapper($lhs, $rhs, $i);
        }

        return $this;
    }

    /**
     * Check if the given source array is sequential (indexed from 0 to n-1).
     *
     * If the array is not sequential, a ValueError is thrown indicating that
     * a view cannot be created for a non-sequential array.
     *
     * @param mixed $source The source array to check for sequential indexing.
     *
     * @return void
     *
     * @throws ValueError if the source array is not sequential.
     */
    protected function checkSequentialArgument($source): void
    {
        if ($source instanceof ArrayViewInterface) {
            return;
        }

        if (\is_array($source) && !Util::isArraySequential($source)) {
            throw new ValueError('Argument is not sequential.');
        }
    }

    /**
     * Util function for checking and converting data argument.
     *
     * @template U Type of $data items.
     *
     * @param array<U>|ArrayViewInterface<U>|U $data The rhs values for a binary operation.
     *
     * @return array<U> converted data.
     */
    protected function checkAndConvertArgument($data): array
    {
        $this->checkSequentialArgument($data);

        if ($data instanceof ArrayViewInterface) {
            $data = $data->toArray();
        } elseif (!\is_array($data)) {
            $data = \array_fill(0, \count($this), $data);
        }

        [$dataSize, $thisSize] = [\count($data), \count($this)];
        if ($dataSize !== $thisSize) {
            throw new SizeError("Length of values array not equal to view length ({$dataSize} != {$thisSize}).");
        }

        return $data;
    }
}

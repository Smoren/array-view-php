<?php

declare(strict_types=1);

namespace Smoren\ArrayView\Views;

use Smoren\ArrayView\Exceptions\IndexError;
use Smoren\ArrayView\Exceptions\SizeError;
use Smoren\ArrayView\Exceptions\ReadonlyError;
use Smoren\ArrayView\Exceptions\ValueError;
use Smoren\ArrayView\Interfaces\ArraySelectorInterface;
use Smoren\ArrayView\Interfaces\ArrayViewInterface;
use Smoren\ArrayView\Interfaces\MaskSelectorInterface;
use Smoren\ArrayView\Selectors\MaskSelector;
use Smoren\ArrayView\Selectors\SliceSelector;
use Smoren\ArrayView\Traits\ArrayViewAccessTrait;
use Smoren\ArrayView\Util;

/**
 * Class representing a view of an array or another array view
 * with additional methods for filtering, mapping, and transforming the data.
 *
 * ```php
 * $source = [1, 2, 3, 4, 5];
 * $view = ArrayView::toView($source);
 * ```
 *
 * @template T Type of array source elements.
 *
 * @implements ArrayViewInterface<T>
 */
class ArrayView implements ArrayViewInterface
{
    /**
     * @use ArrayViewAccessTrait<T> for array access methods.
     */
    use ArrayViewAccessTrait;

    /**
     * @var array<T>|ArrayViewInterface<T> The source array or view.
     */
    protected $source;
    /**
     * @var bool Flag indicating if the view is readonly.
     */
    protected bool $readonly;
    /**
     * @var ArrayViewInterface<T>|null The parent view of the current view.
     */
    protected ?ArrayViewInterface $parentView;

    /**
     * Creates an ArrayView instance from the given source array or ArrayView.
     *
     * * If the source is not an ArrayView, a new ArrayView is created with the provided source.
     * * If the source is an ArrayView and the `readonly` parameter is specified as `true`,
     * a new readonly ArrayView is created.
     * * If the source is an ArrayView and it is already readonly, the same ArrayView is returned.
     *
     * ##### Example
     * ```php
     * $source = [1, 2, 3, 4, 5];
     * $view = ArrayView::toView($source);
     *
     * $view[0]; // 1
     * $view['1::2']; // [2, 4]
     * $view['1::2'] = [22, 44];
     *
     * $view->toArray(); // [1, 22, 3, 44, 5]
     * $source; // [1, 22, 3, 44, 5]
     * ```
     *
     * ##### Readonly example
     * ```php
     * $source = [1, 2, 3, 4, 5];
     * $view = ArrayView::toView($source, true);
     *
     * $view['1::2']; // [2, 4]
     * $view['1::2'] = [22, 44]; // throws ReadonlyError
     * $view[0] = 11; // throws ReadonlyError
     * ```
     *
     * @param array<T>|ArrayViewInterface<T> $source The source array or ArrayView to create a view from.
     * @param bool|null $readonly Optional flag to indicate whether the view should be readonly.
     *
     * @return ArrayViewInterface<T> An ArrayView instance based on the source array or ArrayView.
     *
     * @throws ValueError if the array is not sequential.
     * @throws ReadonlyError if the source is readonly and trying to create a non-readonly view.
     */
    public static function toView(&$source, ?bool $readonly = null): ArrayViewInterface
    {
        if (!($source instanceof ArrayViewInterface)) {
            return new ArrayView($source, $readonly);
        }

        if (!$source->isReadonly() && $readonly) {
            return new ArrayView($source, $readonly);
        }

        return $source;
    }

    /**
     * {@inheritDoc}
     *
     * ##### Example:
     * ```php
     * $source = [1, 2, 3, 4, 5];
     * $view = ArrayView::toUnlinkedView($source);
     *
     * $view[0]; // 1
     * $view['1::2']; // [2, 4]
     * $view['1::2'] = [22, 44];
     *
     * $view->toArray(); // [1, 22, 3, 44, 5]
     * $source; // [1, 2, 3, 4, 5]
     * ```
     *
     * ##### Readonly example:
     * ```php
     * $source = [1, 2, 3, 4, 5];
     * $view = ArrayView::toUnlinkedView($source, true);
     *
     * $view['1::2']; // [2, 4]
     * $view['1::2'] = [22, 44]; // throws ReadonlyError
     * $view[0] = 11; // throws ReadonlyError
     * ```
     */
    public static function toUnlinkedView($source, ?bool $readonly = null): ArrayViewInterface
    {
        return static::toView($source, $readonly);
    }

    /**
     * Constructor to create a new ArrayView.
     *
     * * If the source is not an ArrayView, a new ArrayView is created with the provided source.
     * * If the source is an ArrayView and the `readonly` parameter is specified as `true`,
     * a new readonly ArrayView is created.
     * * If the source is an ArrayView and it is already readonly, the same ArrayView is returned.
     *
     * @param array<T>|ArrayViewInterface<T> $source The source array or view.
     * @param bool|null $readonly Flag indicating if the view is readonly.
     *
     * @throws ValueError if the array is not sequential.
     * @throws ReadonlyError if the source is readonly and trying to create a non-readonly view.
     *
     * @see ArrayView::toView() for creating views.
     */
    public function __construct(&$source, ?bool $readonly = null)
    {
        $this->checkSequential($source);

        $this->source = &$source;
        $this->readonly = $readonly ?? (($source instanceof ArrayViewInterface) ? $source->isReadonly() : false);
        $this->parentView = ($source instanceof ArrayViewInterface) ? $source : null;

        if (($source instanceof ArrayViewInterface) && $source->isReadonly() && !$this->isReadonly()) {
            throw new ReadonlyError("Cannot create non-readonly view for readonly source.");
        }
    }

    /**
     * Returns the array representation of the view.
     *
     * ##### Example
     * ```php
     * $source = [1, 2, 3, 4, 5];
     * $view = ArrayView::toView($source);
     * $view->toArray(); // [1, 2, 3, 4, 5]
     * ```
     *
     * @return array<T> The array representation of the view.
     */
    public function toArray(): array
    {
        return [...$this];
    }

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
     */
    public function is(callable $predicate): MaskSelectorInterface
    {
        $data = $this->toArray();
        return new MaskSelector(array_map($predicate, $data, array_keys($data)));
    }

    /**
     * Returns a subview of this view based on a selector or string slice.
     *
     * ##### Example
     * ```
     * $source = [1, 2, 3, 4, 5, 6, 7, 8, 9, 10];
     *
     * $subview = ArrayView::toView($source)
     *     ->subview(new SliceSelector('::2'))                          // [1, 3, 5, 7, 9]
     *     ->subview(new MaskSelector([true, false, true, true, true])) // [1, 5, 7, 9]
     *     ->subview(new IndexListSelector([0, 1, 2]))                  // [1, 5, 7]
     *     ->subview('1:');                                             // [5, 7]
     *
     * $subview[':'] = [55, 77];
     * print_r($source); // [1, 2, 3, 4, 55, 6, 77, 8, 9, 10]
     * ```
     *
     * ##### Readonly example
     * ```
     * $source = [1, 2, 3, 4, 5, 6, 7, 8, 9, 10];
     * $subview = ArrayView::toView($source)->subview('::2');
     *
     * $subview[':']; // [1, 3, 5, 7, 9]
     * $subview[':'] = [11, 33, 55, 77, 99]; // throws ReadonlyError
     * $subview[0] = [11]; // throws ReadonlyError
     * ```
     *
     * @param ArraySelectorInterface|string $selector The selector or string to filter the subview.
     * @param bool|null $readonly Flag indicating if the subview should be read-only.
     *
     * @return ArrayViewInterface<T> A new view representing the subview of this view.
     *
     * @throws IndexError if the selector is IndexListSelector and some indexes are out of range.
     * @throws SizeError if the selector is MaskSelector and size of the mask not equals to size of the view.
     */
    public function subview($selector, bool $readonly = null): ArrayViewInterface
    {
        return is_string($selector)
            ? (new SliceSelector($selector))->select($this, $readonly)
            : $selector->select($this, $readonly);
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
     * $subview->applyWith($data, fn ($lhs, $rhs) => $lhs * $rhs);
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
        $this->checkSequential($data);

        [$dataSize, $thisSize] = [\count($data), \count($this)];
        if ($dataSize !== $thisSize) {
            throw new SizeError("Length of values array not equal to view length ({$dataSize} != {$thisSize}).");
        }

        $dataView = ArrayView::toView($data);

        $size = \count($this);
        for ($i = 0; $i < $size; $i++) {
            /** @var T $lhs */
            $lhs = $this[$i];
            /** @var U $rhs */
            $rhs = $dataView[$i];
            $this[$i] = $mapper($lhs, $rhs, $i);
        }

        return $this;
    }

    /**
     * Sets new values for the elements in the view.
     *
     * ##### Example
     * ```php
     * $source = [1, 2, 3, 4, 5];
     * $subview = ArrayView::toView($source)->subview('::2'); // [1, 3, 5]
     *
     * $subview->set([11, 33, 55]);
     * $subview->toArray(); // [11, 33, 55]
     *
     * $source; // [11, 2, 33, 4, 55]
     * ```
     *
     * @param array<T>|ArrayViewInterface<T>|T $newValues The new values to set.
     *
     * @return ArrayView<T> this view.
     *
     * @throws ValueError if the $newValues is not sequential array.
     * @throws SizeError if size of $newValues not equals to size of the view.
     */
    public function set($newValues): self
    {
        $this->checkSequential($newValues);

        if (!\is_array($newValues) && !($newValues instanceof ArrayViewInterface)) {
            $size = \count($this);
            for ($i = 0; $i < $size; $i++) {
                $this[$i] = $newValues;
            }
            return $this;
        }

        [$dataSize, $thisSize] = [\count($newValues), \count($this)];
        if ($dataSize !== $thisSize) {
            throw new SizeError("Length of values array not equal to view length ({$dataSize} != {$thisSize}).");
        }

        $newValuesView = ArrayView::toView($newValues);

        $size = \count($this);
        for ($i = 0; $i < $size; $i++) {
            $this[$i] = $newValuesView[$i];
        }

        return $this;
    }

    /**
     * Return iterator to iterate the view elements.
     *
     * ##### Example
     * ```php
     * $source = [1, 2, 3, 4, 5];
     * $subview = ArrayView::toView($source)->subview('::2'); // [1, 3, 5]
     *
     * foreach ($subview as $item) {
     *     // 1, 3, 5
     * }
     *
     * print_r([...$subview]); // [1, 3, 5]
     * ```
     *
     * @return \Generator<int, T>
     */
    public function getIterator(): \Generator
    {
        $size = \count($this);
        for ($i = 0; $i < $size; $i++) {
            /** @var T $item */
            $item = $this[$i];
            yield $item;
        }
    }

    /**
     * Return true if view is readonly, otherwise false.
     *
     * ##### Example
     * ```php
     * $source = [1, 2, 3, 4, 5];
     *
     * $readonlyView = ArrayView::toView($source, true);
     * $readonlyView->isReadonly(); // true
     *
     * $readonlySubview = ArrayView::toView($source)->subview('::2', true);
     * $readonlySubview->isReadonly(); // true
     *
     * $view = ArrayView::toView($source);
     * $view->isReadonly(); // false
     *
     * $subview = ArrayView::toView($source)->subview('::2');
     * $subview->isReadonly(); // false
     * ```
     *
     * @return bool
     */
    public function isReadonly(): bool
    {
        return $this->readonly;
    }

    /**
     * Return size of the view.
     *
     * ##### Example
     * ```php
     * $source = [1, 2, 3, 4, 5];
     *
     * $subview = ArrayView::toView($source)->subview('::2'); // [1, 3, 5]
     * count($subview); // 3
     * ```
     *
     * @return int
     */
    public function count(): int
    {
        return $this->getParentSize();
    }

    /**
     * Get the size of the parent view or source array.
     *
     * @return int The size of the parent view or source array.
     */
    protected function getParentSize(): int
    {
        return ($this->parentView !== null)
            ? \count($this->parentView)
            : \count($this->source);
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
    protected function checkSequential($source): void
    {
        if (is_array($source) && !Util::isArraySequential($source)) {
            throw new ValueError('Cannot create view for non-sequential array.');
        }
    }

    /**
     * Convert the given index to a valid index within the source array.
     *
     * @param int $i The index to convert.
     *
     * @return int The converted index within the source array.
     *
     * @throws IndexError if the index is out of range and $throwError is true.
     */
    protected function convertIndex(int $i): int
    {
        return Util::normalizeIndex($i, \count($this->source));
    }

    /**
     * Check if a numeric offset exists in the source array.
     *
     * @param numeric $offset The numeric offset to check.
     *
     * @return bool Returns true if the numeric offset exists in the source, false otherwise.
     */
    private function numericOffsetExists($offset): bool
    {
        if (!\is_string($offset) && \is_numeric($offset) && (\is_nan($offset) || \is_infinite($offset))) {
            return false;
        }

        if (\is_numeric($offset) && !\is_integer($offset + 0)) {
            return false;
        }

        try {
            $index = $this->convertIndex(intval($offset));
        } catch (IndexError $e) {
            return false;
        }
        return \is_array($this->source)
            ? \array_key_exists($index, $this->source)
            : $this->source->offsetExists($index);
    }
}

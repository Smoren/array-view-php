<?php

declare(strict_types=1);

namespace Smoren\ArrayView\Interfaces;

use Smoren\ArrayView\Exceptions\IndexError;
use Smoren\ArrayView\Exceptions\KeyError;
use Smoren\ArrayView\Exceptions\NotSupportedError;
use Smoren\ArrayView\Exceptions\ReadonlyError;
use Smoren\ArrayView\Exceptions\SizeError;
use Smoren\ArrayView\Exceptions\ValueError;

/**
 * Interface for a view of an array with additional methods
 * for filtering, mapping, and transforming the data.
 *
 * @template T The type of elements in the array
 *
 * @extends \ArrayAccess<int|string|array<int|bool>|ArrayViewInterface<int|bool>|ArraySelectorInterface, T|array<T>>
 * @extends \IteratorAggregate<int, T>
 */
interface ArrayViewInterface extends \ArrayAccess, \IteratorAggregate, \Countable
{
    /**
     * Creates an ArrayView instance from the given source array or ArrayView.
     *
     * * If the source is not an ArrayView, a new ArrayView is created with the provided source.
     * * If the source is an ArrayView and the `readonly` parameter is specified as `true`,
     * a new readonly ArrayView is created.
     * * If the source is an ArrayView and it is already readonly, the same ArrayView is returned.
     *
     * @param array<T>|ArrayViewInterface<T> $source The source array or ArrayView to create a view from.
     * @param bool|null $readonly Optional flag to indicate whether the view should be readonly.
     *
     * @return ArrayViewInterface<T> An ArrayView instance based on the source array or ArrayView.
     *
     * @throws ValueError if the array is not sequential.
     * @throws ReadonlyError if the source is readonly and trying to create a non-readonly view.
     */
    public static function toView(&$source, ?bool $readonly = null): ArrayViewInterface;

    /**
     * Creates an unlinked from source ArrayView instance from the given source array or ArrayView.
     *
     * * If the source is not an ArrayView, a new ArrayView is created with the provided source.
     * * If the source is an ArrayView and the `readonly` parameter is specified as `true`,
     * a new readonly ArrayView is created.
     * * If the source is an ArrayView and it is already readonly, the same ArrayView is returned.
     *
     * @param array<T>|ArrayViewInterface<T> $source The source array or ArrayView to create a view from.
     * @param bool|null $readonly Optional flag to indicate whether the view should be readonly.
     *
     * @return ArrayViewInterface<T> An ArrayView instance based on the source array or ArrayView.
     *
     * @throws ValueError if the array is not sequential.
     * @throws ReadonlyError if the source is readonly and trying to create a non-readonly view.
     */
    public static function toUnlinkedView($source, ?bool $readonly = null): ArrayViewInterface;

    /**
     * Returns the array representation of the view.
     *
     * @return array<T> The array representation of the view.
     */
    public function toArray(): array;

    /**
     * Filters the elements in the view based on a predicate function.
     *
     * @param callable(T, int): bool $predicate Function that returns a boolean value for each element.
     *
     * @return ArrayViewInterface<T> A new view with elements that satisfy the predicate.
     */
    public function filter(callable $predicate): ArrayViewInterface;

    /**
     * Checks if all elements in the view satisfy a given predicate function.
     *
     * @param callable(T, int): bool $predicate Function that returns a boolean value for each element.
     *
     * @return MaskSelectorInterface Boolean mask for selecting elements that satisfy the predicate.
     */
    public function is(callable $predicate): MaskSelectorInterface;

    /**
     * Compares the elements of the current ArrayView instance with another array or ArrayView
     * using the provided comparator function.
     *
     * @template U The type of the elements in the array for comparison with.
     *
     * @param array<U>|ArrayViewInterface<U> $data The array or ArrayView to compare to.
     * @param callable(T, U, int): bool $comparator Function that determines the comparison logic between the elements.
     *
     * @return MaskSelectorInterface A MaskSelector instance representing the results of the element comparisons.
     *
     * @throws ValueError if the $data is not sequential array.
     * @throws SizeError if size of $data not equals to size of the view.
     */
    public function matchWith($data, callable $comparator): MaskSelectorInterface;

    /**
     * Returns a subview of this view based on a selector or string slice.
     *
     * @param string|array<int|bool>|ArrayViewInterface<int|bool>|ArraySelectorInterface $selector The selector or
     * string to filter the subview.
     * @param bool|null $readonly Flag indicating if the subview should be read-only.
     *
     * @return ArrayViewInterface<T> A new view representing the subview of this view.
     *
     * @throws IndexError if the selector is IndexListSelector and some indexes are out of range.
     * @throws SizeError if the selector is MaskSelector and size of the mask not equals to size of the view.
     */
    public function subview($selector, bool $readonly = null): ArrayViewInterface;

    /**
     * Applies a transformation function to each element in the view.
     *
     * @param callable(T, int): T $mapper Function to transform each element.
     *
     * @return ArrayViewInterface<T> this view.
     */
    public function apply(callable $mapper): self;

    /**
     * Applies a transformation function using another array or view as rhs values for a binary operation.
     *
     * @template U The type rhs of a binary operation.
     *
     * @param array<U>|ArrayViewInterface<U> $data The rhs values for a binary operation.
     * @param callable(T, U, int): T $mapper Function to transform each pair of elements.
     *
     * @return ArrayViewInterface<T> this view.
     *
     * @throws ValueError if the $data is not sequential array.
     * @throws SizeError if size of $data not equals to size of the view.
     */
    public function applyWith($data, callable $mapper): self;

    /**
     * Sets new values for the elements in the view.
     *
     * @param array<T>|ArrayViewInterface<T>|T $newValues The new values to set.
     *
     * @return ArrayViewInterface<T> this view.
     *
     * @throws ValueError if the $newValues is not sequential array.
     * @throws SizeError if size of $newValues not equals to size of the view.
     */
    public function set($newValues): self;

    /**
     * Return true if view is readonly, otherwise false.
     *
     * @return bool
     */
    public function isReadonly(): bool;

    /**
     * Return size of the view.
     *
     * @return int
     */
    public function count(): int;

    /**
     * @param numeric|string|ArraySelectorInterface $offset
     *
     * @return bool
     *
     * {@inheritDoc}
     */
    public function offsetExists($offset): bool;

    /**
     * @param numeric|string|ArraySelectorInterface $offset
     *
     * @return T|array<T>
     *
     * @throws IndexError if the offset is out of range.
     * @throws KeyError if the key is invalid.
     *
     * {@inheritDoc}
     */
    #[\ReturnTypeWillChange]
    public function offsetGet($offset);

    /**
     * @param numeric|string|ArraySelectorInterface $offset
     * @param T|array<T>|ArrayViewInterface<T> $value
     *
     * @return void
     *
     * @throws IndexError if the offset is out of range.
     * @throws KeyError if the key is invalid.
     * @throws ReadonlyError if the object is readonly.
     *
     * {@inheritDoc}
     */
    public function offsetSet($offset, $value): void;

    /**
     * @param numeric|string|ArraySelectorInterface $offset
     *
     * @return void
     *
     * @throws NotSupportedError always.
     *
     * {@inheritDoc}
     */
    public function offsetUnset($offset): void;

    /**
     * Return iterator to iterate the view elements.
     *
     * @return \Generator<int, T>
     */
    public function getIterator(): \Generator;
}

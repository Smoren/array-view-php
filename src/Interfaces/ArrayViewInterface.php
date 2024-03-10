<?php

declare(strict_types=1);

namespace Smoren\ArrayView\Interfaces;

use Smoren\ArrayView\Views\ArrayView;

/**
 * @template T
 * @extends \ArrayAccess<int, T|array<T>>
 * @extends \IteratorAggregate<int, T>
 */
interface ArrayViewInterface extends \ArrayAccess, \IteratorAggregate, \Countable
{
    /**
     * @param array<T>|ArrayViewInterface<T> $source
     * @param bool|null $readonly
     * @return ArrayViewInterface<T>
     */
    public static function toView(&$source, ?bool $readonly = null): ArrayViewInterface;

    /**
     * @return array<T>
     */
    public function toArray(): array;

    /**
     * @param callable(T): bool $predicate
     * @return ArrayViewInterface<T>
     */
    public function filter(callable $predicate): ArrayViewInterface;

    /**
     * @param callable(T): bool $predicate
     * @return ArraySelectorInterface
     */
    public function is(callable $predicate): ArraySelectorInterface;

    /**
     * @param ArraySelectorInterface|string $selector
     * @param bool|null $readonly
     * @return ArrayViewInterface<T>
     */
    public function subview($selector, bool $readonly = null): ArrayViewInterface;

    /**
     * @param callable(T, int): T $mapper
     *
     * @return ArrayViewInterface<T>
     */
    public function apply(callable $mapper): self;

    /**
     * @template U
     *
     * @param array<U>|ArrayViewInterface<U> $data
     * @param callable(T, U, int): T $mapper
     *
     * @return ArrayViewInterface<T>
     */
    public function applyWith($data, callable $mapper): self;

    /**
     * @param array<T>|ArrayViewInterface<T>|T $newValues
     *
     * @return ArrayViewInterface<T>
     */
    public function set($newValues): self;

    /**
     * @return bool
     */
    public function isReadonly(): bool;
}

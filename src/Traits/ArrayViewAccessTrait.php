<?php

declare(strict_types=1);

namespace Smoren\ArrayView\Traits;

use Smoren\ArrayView\Exceptions\IndexError;
use Smoren\ArrayView\Exceptions\KeyError;
use Smoren\ArrayView\Exceptions\NotSupportedError;
use Smoren\ArrayView\Exceptions\ReadonlyError;
use Smoren\ArrayView\Interfaces\ArraySelectorInterface;
use Smoren\ArrayView\Interfaces\ArrayViewInterface;
use Smoren\ArrayView\Selectors\IndexListSelector;
use Smoren\ArrayView\Selectors\MaskSelector;
use Smoren\ArrayView\Selectors\SliceSelector;
use Smoren\ArrayView\Structs\Slice;
use Smoren\ArrayView\Util;

/**
 * Trait providing methods for accessing elements in ArrayView object.
 * The trait implements methods for accessing, retrieving, setting,
 * and unsetting elements in the ArrayView object.
 *
 * @template T Type of ArrayView values.
 * @template S of string|array<int|bool>|ArrayViewInterface<int|bool>|ArraySelectorInterface Selector type.
 */
trait ArrayViewAccessTrait
{
    /**
     * Check if the specified offset exists in the ArrayView object.
     *
     * @param numeric|S $offset The offset to check.
     *
     * @return bool
     *
     * {@inheritDoc}
     */
    public function offsetExists($offset): bool
    {
        if (\is_numeric($offset)) {
            return $this->numericOffsetExists($offset);
        }

        try {
            return $this->toSelector($offset)->compatibleWith($this);
        } catch (KeyError $e) {
            return false;
        }
    }

    /**
     * Get the value at the specified offset in the ArrayView object.
     *
     * @param numeric|S $offset The offset to get the value from.
     *
     * @return T|array<T> The value at the specified offset.
     *
     * @throws IndexError if the offset is out of range.
     * @throws KeyError if the key is invalid.
     *
     * {@inheritDoc}
     */
    #[\ReturnTypeWillChange]
    public function offsetGet($offset)
    {
        if (\is_numeric($offset)) {
            if (!$this->numericOffsetExists($offset)) {
                throw new IndexError("Index {$offset} is out of range.");
            }
            return $this->source[$this->convertIndex(\intval($offset))];
        }

        return $this->subview($this->toSelector($offset))->toArray();
    }

    /**
     * Set the value at the specified offset in the ArrayView object.
     *
     * @param numeric|S $offset The offset to set the value at.
     * @param T|array<T>|ArrayViewInterface<T> $value The value to set.
     *
     * @return void
     *
     * @throws IndexError if the offset is out of range.
     * @throws KeyError if the key is invalid.
     * @throws ReadonlyError if the object is readonly.
     *
     * {@inheritDoc}
     */
    public function offsetSet($offset, $value): void
    {
        if ($this->isReadonly()) {
            throw new ReadonlyError("Cannot modify a readonly view.");
        }

        if (!\is_numeric($offset)) {
            $this->subview($this->toSelector($offset))->set($value);
            return;
        }

        if (!$this->numericOffsetExists($offset)) {
            throw new IndexError("Index {$offset} is out of range.");
        }

        // @phpstan-ignore-next-line
        $this->source[$this->convertIndex(\intval($offset))] = $value;
    }

    /**
     * Unset the value at the specified offset in the array-like object.
     *
     * @param numeric|S $offset The offset to unset the value at.
     *
     * @return void
     *
     * @throws NotSupportedError always.
     *
     * {@inheritDoc}
     */
    public function offsetUnset($offset): void
    {
        throw new NotSupportedError();
    }

    /**
     * Converts array to selector.
     *
     * @param S $input value to convert.
     *
     * @return ArraySelectorInterface
     */
    protected function toSelector($input): ArraySelectorInterface
    {
        if ($input instanceof ArraySelectorInterface) {
            return $input;
        }

        if (\is_string($input) && Slice::isSlice($input)) {
            return new SliceSelector($input);
        }

        if ($input instanceof ArrayViewInterface) {
            $input = $input->toArray();
        }

        if (!\is_array($input) || !Util::isArraySequential($input)) {
            $strOffset = \is_scalar($input) ? \strval($input) : \gettype($input);
            throw new KeyError("Invalid key: \"{$strOffset}\".");
        }

        if (\count($input) > 0 && \is_bool($input[0])) {
            /** @var array<bool> $input */
            return new MaskSelector($input);
        }

        /** @var array<int> $input */
        return new IndexListSelector($input);
    }
}

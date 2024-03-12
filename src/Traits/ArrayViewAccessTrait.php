<?php

declare(strict_types=1);

namespace Smoren\ArrayView\Traits;

use Smoren\ArrayView\Exceptions\IndexError;
use Smoren\ArrayView\Exceptions\KeyError;
use Smoren\ArrayView\Exceptions\NotSupportedError;
use Smoren\ArrayView\Exceptions\ReadonlyError;
use Smoren\ArrayView\Interfaces\ArraySelectorInterface;
use Smoren\ArrayView\Interfaces\ArrayViewInterface;
use Smoren\ArrayView\Selectors\SliceSelector;
use Smoren\ArrayView\Structs\Slice;

/**
 * Trait providing methods for accessing elements in ArrayView object.
 * The trait implements methods for accessing, retrieving, setting,
 * and unsetting elements in the ArrayView object.
 *
 * @template T Type of ArrayView values.
 */
trait ArrayViewAccessTrait
{
    /**
     * Check if the specified offset exists in the ArrayView object.
     *
     * @param numeric|string|ArraySelectorInterface $offset The offset to check.
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

        if (\is_string($offset) && Slice::isSlice($offset)) {
            return true;
        }

        if ($offset instanceof ArraySelectorInterface) {
            return true;
        }

        return false;
    }

    /**
     * Get the value at the specified offset in the ArrayView object.
     *
     * @param numeric|string|ArraySelectorInterface $offset The offset to get the value from.
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
        /** @var mixed $offset */
        if (\is_numeric($offset)) {
            if (!$this->numericOffsetExists($offset)) {
                throw new IndexError("Index {$offset} is out of range.");
            }
            return $this->source[$this->convertIndex(\intval($offset))];
        }

        if (\is_string($offset) && Slice::isSlice($offset)) {
            return $this->subview(new SliceSelector($offset))->toArray();
        }

        if ($offset instanceof ArraySelectorInterface) {
            return $this->subview($offset)->toArray();
        }

        $strOffset = \is_scalar($offset) ? \strval($offset) : \gettype($offset);
        throw new KeyError("Invalid key: \"{$strOffset}\".");
    }

    /**
     * Set the value at the specified offset in the ArrayView object.
     *
     * @param numeric|string|ArraySelectorInterface $offset The offset to set the value at.
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
        /** @var mixed $offset */
        if ($this->isReadonly()) {
            throw new ReadonlyError("Cannot modify a readonly view.");
        }

        if (\is_numeric($offset)) {
            if (!$this->numericOffsetExists($offset)) {
                throw new IndexError("Index {$offset} is out of range.");
            }

            // @phpstan-ignore-next-line
            $this->source[$this->convertIndex(\intval($offset))] = $value;
            return;
        }

        if (\is_string($offset) && Slice::isSlice($offset)) {
            /** @var array<T>|ArrayViewInterface<T> $value */
            $this->subview(new SliceSelector($offset))->set($value);
            return;
        }

        if ($offset instanceof ArraySelectorInterface) {
            $this->subview($offset)->set($value);
            return;
        }

        $strOffset = \is_scalar($offset) ? \strval($offset) : \gettype($offset);
        throw new KeyError("Invalid key: \"{$strOffset}\".");
    }

    /**
     * Unset the value at the specified offset in the array-like object.
     *
     * @param numeric|string|ArraySelectorInterface $offset The offset to unset the value at.
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
}

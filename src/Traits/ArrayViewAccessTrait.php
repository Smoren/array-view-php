<?php

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
 * @template T
 */
trait ArrayViewAccessTrait
{
    /**
     * @param numeric|string|ArraySelectorInterface $offset
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
     * @param numeric|string|ArraySelectorInterface $offset
     *
     * @return T|array<T>
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
     * @param numeric|string|ArraySelectorInterface $offset
     * @param T|array<T>|ArrayViewInterface<T> $value
     *
     * @return void
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
     * @param numeric|string|ArraySelectorInterface $offset
     *
     * @return void
     *
     * @throws NotSupportedError
     *
     * {@inheritDoc}
     */
    public function offsetUnset($offset): void
    {
        throw new NotSupportedError();
    }
}

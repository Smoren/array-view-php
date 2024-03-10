<?php

declare(strict_types=1);

namespace Smoren\ArrayView\Views;

use Smoren\ArrayView\Exceptions\IndexError;
use Smoren\ArrayView\Exceptions\KeyError;
use Smoren\ArrayView\Exceptions\LengthError;
use Smoren\ArrayView\Exceptions\NotSupportedError;
use Smoren\ArrayView\Exceptions\ReadonlyError;
use Smoren\ArrayView\Exceptions\ValueError;
use Smoren\ArrayView\Interfaces\ArraySelectorInterface;
use Smoren\ArrayView\Interfaces\ArrayViewInterface;
use Smoren\ArrayView\Selectors\MaskSelector;
use Smoren\ArrayView\Selectors\SliceSelector;
use Smoren\ArrayView\Structs\Slice;
use Smoren\ArrayView\Util;

/**
 * @template T
 *
 * @implements ArrayViewInterface<T>
 */
class ArrayView implements ArrayViewInterface
{
    /**
     * @var array<T>|ArrayViewInterface<T>
     */
    protected $source;
    /**
     * @var bool
     */
    protected bool $readonly;
    /**
     * @var ArrayViewInterface<T>|null
     */
    protected ?ArrayViewInterface $parentView;

    /**
     * {@inheritDoc}
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
     * @param array<T>|ArrayViewInterface<T> $source
     * @param bool|null $readonly
     * @throws ReadonlyError
     */
    public function __construct(&$source, ?bool $readonly = null)
    {
        if (is_array($source) && !Util::isArraySequential($source)) {
            throw new ValueError('Cannot create view for non-sequential array.');
        }

        $this->source = &$source;
        $this->readonly = $readonly ?? (($source instanceof ArrayViewInterface) ? $source->isReadonly() : false);
        $this->parentView = ($source instanceof ArrayViewInterface) ? $source : null;

        if (($source instanceof ArrayViewInterface) && $source->isReadonly() && !$this->isReadonly()) {
            throw new ReadonlyError("Cannot create non-readonly view for readonly source.");
        }
    }

    /**
     * {@inheritDoc}
     */
    public function toArray(): array
    {
        return [...$this];
    }

    /**
     * {@inheritDoc}
     */
    public function filter(callable $predicate): ArrayViewInterface
    {
        return $this->is($predicate)->select($this);
    }

    /**
     * {@inheritDoc}
     */
    public function is(callable $predicate): ArraySelectorInterface
    {
        return new MaskSelector(array_map($predicate, $this->toArray()));
    }

    /**
     * {@inheritDoc}
     */
    public function subview($selector, bool $readonly = null): ArrayViewInterface
    {
        return is_string($selector)
            ? (new SliceSelector($selector))->select($this, $readonly)
            : $selector->select($this, $readonly);
    }

    /**
     * @return ArrayView<T>
     *
     * {@inheritDoc}
     */
    public function apply(callable $mapper): self
    {
        for ($i = 0; $i < \count($this); $i++) {
            /** @var T $item */
            $item = $this[$i];
            $this[$i] = $mapper($item, $i);
        }
        return $this;
    }

    /**
     * @template U
     *
     * @param array<U>|ArrayViewInterface<U> $data
     * @param callable(T, U, int): T $mapper
     *
     * @return ArrayView<T>
     *
     * {@inheritDoc}
     */
    public function applyWith($data, callable $mapper): ArrayViewInterface
    {
        [$dataSize, $thisSize] = [\count($data), \count($this)];
        if ($dataSize !== $thisSize) {
            throw new LengthError("Length of values array not equal to view length ({$dataSize} != {$thisSize}).");
        }

        $dataView = ArrayView::toView($data);

        for ($i = 0; $i < \count($this); $i++) {
            /** @var T $lhs */
            $lhs = $this[$i];
            /** @var U $rhs */
            $rhs = $dataView[$i];
            $this[$i] = $mapper($lhs, $rhs, $i);
        }

        return $this;
    }

    /**
     * {@inheritDoc}
     *
     * @return ArrayViewInterface<T>
     */
    public function set($newValues): ArrayViewInterface
    {
        if (!\is_array($newValues) && !($newValues instanceof ArrayViewInterface)) {
            for ($i = 0; $i < \count($this); $i++) {
                $this[$i] = $newValues;
            }
            return $this;
        }

        [$dataSize, $thisSize] = [\count($newValues), \count($this)];
        if ($dataSize !== $thisSize) {
            throw new LengthError("Length of values array not equal to view length ({$dataSize} != {$thisSize}).");
        }

        $newValuesView = ArrayView::toView($newValues);

        for ($i = 0; $i < \count($this); $i++) {
            // @phpstan-ignore-next-line
            $this[$i] = $newValuesView[$i];
        }

        return $this;
    }

    /**
     * @return \Generator<int, T>
     */
    public function getIterator(): \Generator
    {
        for ($i = 0; $i < \count($this); $i++) {
            /** @var T $item */
            $item = $this[$i];
            yield $item;
        }
    }

    /**
     * @return bool
     */
    public function isReadonly(): bool
    {
        return $this->readonly;
    }

    /**
     * @param numeric|string|ArraySelectorInterface $offset
     * @return bool
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
     * @return T|array<T>
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

        if (\is_string($offset) && Slice::isSlice($offset)) {
            return $this->subview(new SliceSelector($offset))->toArray();
        }

        if ($offset instanceof ArraySelectorInterface) {
            return $this->subview($offset)->toArray();
        }

        throw new KeyError("Invalid key: \"{$offset}\".");
    }

    /**
     * @param numeric|string|ArraySelectorInterface $offset
     * @param T|array<T>|ArrayViewInterface<T> $value
     * @return void
     */
    public function offsetSet($offset, $value): void
    {
        if ($this->isReadonly()) {
            throw new ReadonlyError("Cannot modify a readonly view.");
        }

        if (\is_numeric($offset) && $this->numericOffsetExists($offset)) {
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

        throw new KeyError("Invalid key: \"{$offset}\".");
    }

    /**
     * @param numeric|string|ArraySelectorInterface $offset
     * @return void
     * @throws NotSupportedError
     */
    public function offsetUnset($offset): void
    {
        throw new NotSupportedError();
    }

    /**
     * @return int
     */
    public function count(): int
    {
        return $this->getParentSize();
    }

    protected function getParentSize(): int
    {
        return ($this->parentView !== null)
            ? \count($this->parentView)
            : \count($this->source);
    }

    /**
     * @param int $i
     * @return int
     */
    protected function convertIndex(int $i): int
    {
        return Util::normalizeIndex($i, \count($this->source));
    }

    /**
     * @param numeric $offset
     * @return bool
     */
    private function numericOffsetExists($offset): bool
    {
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

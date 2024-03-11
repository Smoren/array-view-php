<?php

declare(strict_types=1);

namespace Smoren\ArrayView\Views;

use Smoren\ArrayView\Exceptions\IndexError;
use Smoren\ArrayView\Exceptions\SizeError;
use Smoren\ArrayView\Exceptions\ReadonlyError;
use Smoren\ArrayView\Exceptions\ValueError;
use Smoren\ArrayView\Interfaces\ArrayViewInterface;
use Smoren\ArrayView\Interfaces\MaskSelectorInterface;
use Smoren\ArrayView\Selectors\MaskSelector;
use Smoren\ArrayView\Selectors\SliceSelector;
use Smoren\ArrayView\Traits\ArrayViewAccessTrait;
use Smoren\ArrayView\Util;

/**
 * @template T
 *
 * @implements ArrayViewInterface<T>
 */
class ArrayView implements ArrayViewInterface
{
    /**
     * @use ArrayViewAccessTrait<T>
     */
    use ArrayViewAccessTrait;

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
     * {@inheritDoc}
     */
    public static function toUnlinkedView($source, ?bool $readonly = null): ArrayViewInterface
    {
        return static::toView($source, $readonly);
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
    public function is(callable $predicate): MaskSelectorInterface
    {
        $data = $this->toArray();
        return new MaskSelector(array_map($predicate, $data, array_keys($data)));
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
        $size = \count($this);
        for ($i = 0; $i < $size; $i++) {
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
    public function applyWith($data, callable $mapper): self
    {
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
     * {@inheritDoc}
     *
     * @return ArrayView<T>
     */
    public function set($newValues): self
    {
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
     * {@inheritDoc}
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
     * {@inheritDoc}
     */
    public function isReadonly(): bool
    {
        return $this->readonly;
    }

    /**
     * {@inheritDoc}
     */
    public function count(): int
    {
        return $this->getParentSize();
    }

    /**
     * @return int
     */
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
        if (!\is_string($offset) && \is_numeric($offset) && (\is_nan($offset) || \is_infinite($offset))) {
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

<?php

namespace Smoren\ArrayView\Views;

use Smoren\ArrayView\Exceptions\ReadonlyError;
use Smoren\ArrayView\Interfaces\ArrayViewInterface;
use Smoren\ArrayView\Util;

/**
 * @template T
 * @extends ArrayView<T>
 */
class ArrayIndexListView extends ArrayView
{
    /**
     * @var array<int>
     */
    protected array $indexes;

    /**
     * @param array<T>|ArrayViewInterface<T> $source
     * @param array<int> $indexes
     * @param bool|null $readonly
     *
     * @throws ReadonlyError
     */
    public function __construct(&$source, array $indexes, ?bool $readonly = null)
    {
        parent::__construct($source, $readonly);
        $this->indexes = $indexes;
    }

    /**
     * {@inheritDoc}
     */
    public function toArray(): array
    {
        /** @var Array<T> */
        return array_map(fn(int $index) => $this[$index], array_keys($this->indexes));
    }

    /**
     * {@inheritDoc}
     */
    public function count(): int
    {
        return \count($this->indexes);
    }

    /**
     * {@inheritDoc}
     */
    protected function convertIndex(int $i): int
    {
        return Util::normalizeIndex(
            $this->indexes[Util::normalizeIndex($i, \count($this->indexes))],
            $this->getParentSize()
        );
    }
}

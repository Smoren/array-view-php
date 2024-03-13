<?php

declare(strict_types=1);

namespace Smoren\ArrayView\Views;

use Smoren\ArrayView\Exceptions\ReadonlyError;
use Smoren\ArrayView\Exceptions\ValueError;
use Smoren\ArrayView\Interfaces\ArrayViewInterface;
use Smoren\ArrayView\Util;

/**
 * Class representing an index-based view of an array or another ArrayView for accessing elements at specific indexes.
 *
 * Each element in the view is based on the specified indexes.
 *
 * @template T Type of array source elements.
 *
 * <code>
 * <?php
 *
 * $source = [1, 2, 3, 4, 5];
 * $view = ArrayView::toView($source)->subview(new IndexListSelector([0, 2, 4]));
 * $view->toArray(); // [1, 3, 5]
 *
 * </code>
 *
 * @extends ArrayView<T>
 */
class ArrayIndexListView extends ArrayView
{
    /**
     * @var array<int> The indexes array specifying the indexes of elements in the source array to include in the view.
     */
    protected array $indexes;

    /**
     * Constructs a new ArrayIndexListView instance with the specified source array or ArrayView and indexes array.
     *
     * @param array<T>|ArrayViewInterface<T> $source The source array or ArrayView to create a view from.
     * @param array<int> $indexes The indexes array specifying the indexes of elements in the source array.
     * @param bool|null $readonly Optional flag to indicate whether the view should be readonly.
     *
     * @throws ValueError if the array is not sequential.
     * @throws ReadonlyError if the source is readonly and trying to create a non-readonly view.
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

<?php

declare(strict_types=1);

namespace Smoren\ArrayView\Selectors;

use Smoren\ArrayView\Interfaces\ArrayViewInterface;
use Smoren\ArrayView\Interfaces\IndexListSelectorInterface;
use Smoren\ArrayView\Views\ArrayIndexListView;

/**
 * Represents an index list selector that selects elements based on the provided array of indexes.
 */
final class IndexListSelector implements IndexListSelectorInterface
{
    /**
     * @var array<int> The array of indexes to select elements from.
     */
    private array $value;

    /**
     * Creates a new IndexListSelector instance with the provided array of indexes.
     *
     * @param array<int>|ArrayViewInterface<int> $value The array of indexes or array view containing indexes.
     */
    public function __construct($value)
    {
        $this->value = \is_array($value) ? $value : $value->toArray();
    }

    /**
     * Selects elements from the source array based on the index list.
     *
     * @template T The type of elements in the source array view.
     *
     * @param ArrayViewInterface<T> $source The source array view to select elements from.
     * @param bool|null $readonly Whether the selection should be read-only.
     *
     * @return ArrayIndexListView<T> The view containing the selected elements.
     *
     * {@inheritDoc}
     */
    public function select(ArrayViewInterface $source, ?bool $readonly = null): ArrayIndexListView
    {
        return new ArrayIndexListView($source, $this->value, $readonly ?? $source->isReadonly());
    }

    /**
     * {@inheritDoc}
     */
    public function getValue(): array
    {
        return $this->value;
    }
}

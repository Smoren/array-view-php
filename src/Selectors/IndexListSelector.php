<?php

declare(strict_types=1);

namespace Smoren\ArrayView\Selectors;

use Smoren\ArrayView\Exceptions\IndexError;
use Smoren\ArrayView\Interfaces\ArrayViewInterface;
use Smoren\ArrayView\Interfaces\IndexListSelectorInterface;
use Smoren\ArrayView\Views\ArrayIndexListView;

/**
 * Represents an index list selector that selects elements based on the provided array of indexes.
 *
 * ##### Example
 * ```php
 * $originalArray = [1, 2, 3, 4, 5, 6, 7, 8, 9, 10];
 * $view = ArrayView::toView($originalArray);
 *
 * $selector = new IndexListSelector([0, 2, 4]);
 * print_r($view[$selector]); // [1, 3, 5]
 * print_r($view->subview($selector)->toArray()); // [1, 3, 5]
 * ```
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
        if (!$this->compatibleWith($source)) {
            throw new IndexError('Some indexes are out of range.');
        }

        return new ArrayIndexListView($source, $this->value, $readonly ?? $source->isReadonly());
    }

    /**
     * Checks if the selector is compatible with the given view.
     *
     * @template T View elements type.
     *
     * @param ArrayViewInterface<T> $view the view to check compatibility with.
     *
     * @return bool true if the element is compatible, false otherwise
     *
     * {@inheritDoc}
     */
    public function compatibleWith(ArrayViewInterface $view): bool
    {
        return \count($this->value) === 0 || \max($this->value) < \count($view) && \min($this->value) >= -\count($view);
    }

    /**
     * {@inheritDoc}
     */
    public function getValue(): array
    {
        return $this->value;
    }
}

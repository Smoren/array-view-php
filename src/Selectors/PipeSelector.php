<?php

declare(strict_types=1);

namespace Smoren\ArrayView\Selectors;

use Smoren\ArrayView\Interfaces\ArraySelectorInterface;
use Smoren\ArrayView\Interfaces\ArrayViewInterface;
use Smoren\ArrayView\Interfaces\PipeSelectorInterface;
use Smoren\ArrayView\Views\ArrayView;

/**
 * Represents a selector that applies a series of selectors sequentially to a source array view.
 *
 * ##### Example
 * ```php
 * $originalArray = [1, 2, 3, 4, 5, 6, 7, 8, 9, 10];
 * $selector = new PipeSelector([
 *     new SliceSelector('::2'),
 *     new MaskSelector([true, false, true, true, true]),
 *     new IndexListSelector([0, 1, 2]),
 *     new SliceSelector('1:'),
 * ]);
 *
 * $view = ArrayView::toView($originalArray);
 * $subview = $view->subview($selector);
 * print_r($subview[':']); // [5, 7]
 *
 * $subview[':'] = [55, 77];
 * print_r($originalArray); // [1, 2, 3, 4, 55, 6, 77, 8, 9, 10]
 * ```
 */
final class PipeSelector implements PipeSelectorInterface
{
    /**
     * @var array<ArraySelectorInterface> An array of selectors to be applied sequentially.
     */
    private array $selectors;

    /**
     * Creates a new PipeSelector instance with the provided selectors array.
     *
     * @param array<ArraySelectorInterface> $selectors An array of selectors to be assigned to the PipeSelector.
     */
    public function __construct(array $selectors)
    {
        $this->selectors = $selectors;
    }

    /**
     * Applies the series of selectors to the given source array view.
     *
     * @template T The type of elements in the source array view.
     *
     * @param ArrayViewInterface<T> $source The source array view to select from.
     * @param bool|null $readonly Optional parameter to specify if the view should be read-only.
     *
     * @return ArrayViewInterface<T> The resulting array view after applying all selectors.
     */
    public function select(ArrayViewInterface $source, ?bool $readonly = null): ArrayViewInterface
    {
        $view = ArrayView::toView($source, $readonly);
        foreach ($this->selectors as $selector) {
            $view = $selector->select($view, $readonly);
        }
        /** @var ArrayViewInterface<T> $view  */
        return $view;
    }

    /**
     * Checks if the series of selectors are compatible with the given array view.
     *
     * @template T The type of elements in the source array view.
     *
     * @param ArrayViewInterface<T> $view The array view to check compatibility with.
     *
     * @return bool True if all selectors are compatible with the array view, false otherwise.
     */
    public function compatibleWith(ArrayViewInterface $view): bool
    {
        foreach ($this->selectors as $selector) {
            if (!$selector->compatibleWith($view)) {
                return false;
            }
            $view = $selector->select($view);
        }
        return true;
    }

    /**
     * Returns the array of selectors assigned to the PipeSelector.
     *
     * @return array<ArraySelectorInterface> The array of selectors.
     */
    public function getValue(): array
    {
        return $this->selectors;
    }
}

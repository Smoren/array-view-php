<?php

declare(strict_types=1);

namespace Smoren\ArrayView\Interfaces;

/**
 * Interface for selecting elements from an array view.
 */
interface ArraySelectorInterface
{
    /**
     * Selects elements from a source array view based on the selector criteria.
     *
     * @template T The type of elements in the source array view.
     *
     * @param ArrayViewInterface<T> $source The source array view to select elements from.
     * @param bool|null $readonly Flag indicating if the result view should be read-only.
     *
     * @return ArrayViewInterface<T> A new view with selected elements from the source.
     */
    public function select(ArrayViewInterface $source, ?bool $readonly = null): ArrayViewInterface;

    /**
     * Return value of the selector.
     *
     * @return mixed
     */
    public function getValue();
}

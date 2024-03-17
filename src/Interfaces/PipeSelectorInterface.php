<?php

declare(strict_types=1);

namespace Smoren\ArrayView\Interfaces;

/**
 * Interface for selector that applies a series of selectors sequentially to a source array view.
 */
interface PipeSelectorInterface extends ArraySelectorInterface
{
    /**
     * Returns the array of selectors assigned to the PipeSelector.
     *
     * @return array<ArraySelectorInterface>
     */
    public function getValue(): array;
}

<?php

declare(strict_types=1);

namespace Smoren\ArrayView\Interfaces;

/**
 * Interface for selecting elements from an array view by boolean mask.
 */
interface MaskSelectorInterface extends ArraySelectorInterface
{
    /**
     * Return boolean mask array.
     *
     * @return array<bool>
     */
    public function getValue(): array;
}

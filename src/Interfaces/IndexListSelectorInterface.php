<?php

declare(strict_types=1);

namespace Smoren\ArrayView\Interfaces;

/**
 * Interface for selecting elements from an array view by boolean mask.
 */
interface IndexListSelectorInterface extends ArraySelectorInterface
{
    /**
     * Return index list array.
     *
     * @return array<int>
     */
    public function getValue(): array;
}

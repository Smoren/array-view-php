<?php

declare(strict_types=1);

namespace Smoren\ArrayView\Interfaces;

interface IndexListSelectorInterface extends ArraySelectorInterface
{
    /**
     * @return array<int>
     */
    public function getValue(): array;
}

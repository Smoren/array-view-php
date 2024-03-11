<?php

declare(strict_types=1);

namespace Smoren\ArrayView\Interfaces;

interface MaskSelectorInterface extends ArraySelectorInterface
{
    /**
     * @return array<bool>
     */
    public function getValue(): array;
}

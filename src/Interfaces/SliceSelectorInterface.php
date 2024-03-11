<?php

declare(strict_types=1);

namespace Smoren\ArrayView\Interfaces;

use Smoren\ArrayView\Structs\Slice;

interface SliceSelectorInterface extends ArraySelectorInterface
{
    /**
     * @return Slice
     */
    public function getValue(): Slice;
}

<?php

declare(strict_types=1);

namespace Smoren\ArrayView\Interfaces;

interface ArraySelectorInterface
{
    /**
     * @template T
     *
     * @param ArrayViewInterface<T> $source
     * @param bool|null $readonly
     *
     * @return ArrayViewInterface<T>
     */
    public function select(ArrayViewInterface $source, ?bool $readonly = null): ArrayViewInterface;
}

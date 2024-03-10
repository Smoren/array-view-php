<?php

namespace Smoren\ArrayView\Selectors;

use Smoren\ArrayView\Interfaces\ArraySelectorInterface;
use Smoren\ArrayView\Interfaces\ArrayViewInterface;
use Smoren\ArrayView\Views\ArrayMaskView;

class MaskSelector implements ArraySelectorInterface
{
    /**
     * @var array<bool>
     */
    private array $value;

    /**
     * @param array<bool> $value
     */
    public function __construct(array $value)
    {
        $this->value = $value;
    }

    /**
     * @template T
     *
     * @param ArrayViewInterface<T> $source
     * @param bool|null $readonly
     *
     * @return ArrayMaskView<T>
     *
     * {@inheritDoc}
     */
    public function select(ArrayViewInterface $source, ?bool $readonly = null): ArrayMaskView
    {
        return new ArrayMaskView($source, $this->value, $readonly ?? $source->isReadonly());
    }
}

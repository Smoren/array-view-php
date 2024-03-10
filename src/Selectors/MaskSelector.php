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
    private $value;

    /**
     * @param array<bool>|ArrayViewInterface<bool> $value
     */
    public function __construct($value)
    {
        $this->value = \is_array($value) ? $value : $value->toArray();
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

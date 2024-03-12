<?php

declare(strict_types=1);

namespace Smoren\ArrayView\Selectors;

use Smoren\ArrayView\Interfaces\ArrayViewInterface;
use Smoren\ArrayView\Interfaces\MaskSelectorInterface;
use Smoren\ArrayView\Views\ArrayMaskView;

class MaskSelector implements MaskSelectorInterface
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

    /**
     * {@inheritDoc}
     */
    public function getValue(): array
    {
        return $this->value;
    }
}

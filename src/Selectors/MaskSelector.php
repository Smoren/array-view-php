<?php

declare(strict_types=1);

namespace Smoren\ArrayView\Selectors;

use Smoren\ArrayView\Interfaces\ArrayViewInterface;
use Smoren\ArrayView\Interfaces\MaskSelectorInterface;
use Smoren\ArrayView\Views\ArrayMaskView;

/**
 * Represents a mask selector that selects elements based on the provided array of boolean mask values.
 */
class MaskSelector implements MaskSelectorInterface
{
    /**
     * @var array<bool> The array of boolean mask values to select elements based on.
     */
    private $value;

    /**
     * Creates a new MaskSelector instance with the provided array of boolean mask values.
     *
     * @param array<bool>|ArrayViewInterface<bool> $value The array or array view of boolean mask values.
     */
    public function __construct($value)
    {
        $this->value = \is_array($value) ? $value : $value->toArray();
    }

    /**
     * Selects elements from the source array based on the mask values.
     *
     * @template T The type of elements in the source array view.
     *
     * @param ArrayViewInterface<T> $source The source array to select elements from.
     * @param bool|null $readonly Whether the selection should be read-only.
     *
     * @return ArrayMaskView<T> The view containing the selected elements.
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

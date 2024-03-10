<?php

namespace Smoren\ArrayView\Selectors;

use Smoren\ArrayView\Interfaces\ArraySelectorInterface;
use Smoren\ArrayView\Interfaces\ArrayViewInterface;
use Smoren\ArrayView\Views\ArrayIndexListView;

final class IndexListSelector implements ArraySelectorInterface
{
    /**
     * @var array<int>
     */
    private array $value;

    /**
     * @param array<int> $value
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
     * @return ArrayIndexListView<T>
     *
     * {@inheritDoc}
     */
    public function select(ArrayViewInterface $source, ?bool $readonly = null): ArrayIndexListView
    {
        return new ArrayIndexListView($source, $this->value, $readonly ?? $source->isReadonly());
    }
}

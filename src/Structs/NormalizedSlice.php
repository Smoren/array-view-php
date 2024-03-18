<?php

declare(strict_types=1);

namespace Smoren\ArrayView\Structs;

use Smoren\ArrayView\Util;

/**
 * Represents a normalized slice definition with start, end, and step values.
 *
 * @implements \IteratorAggregate<int, int>
 */
class NormalizedSlice extends Slice implements \Countable, \IteratorAggregate
{
    /**
     * Creates a new NormalizedSlice instance with optional start, end, and step values.
     *
     * @param int|null $start The start index of the slice range.
     * @param int|null $end The end index of the slice range.
     * @param int|null $step The step size for selecting elements in the slice range.
     */
    public function __construct(int $start = null, int $end = null, int $step = null)
    {
        parent::__construct($start, $end, $step);
    }

    /**
     * Getter for the start index of the normalized slice.
     *
     * @return int
     */
    public function getStart(): int
    {
        /** @var int */
        return $this->start;
    }

    /**
     * Getter for the stop index of the normalized slice.
     *
     * @return int
     */
    public function getEnd(): int
    {
        /** @var int */
        return $this->end;
    }

    /**
     * Getter for the step of the normalized slice.
     *
     * @return int
     */
    public function getStep(): int
    {
        /** @var int */
        return $this->step;
    }

    /**
     * Return size of the slice range.
     *
     * @return int Size of the slice range.
     */
    public function count(): int
    {
        return \intval(\ceil(\abs((($this->end - $this->start) / $this->step))));
    }

    /**
     * Converts the provided index to the actual index based on the normalized slice parameters.
     *
     * @param int $i The index to convert.
     *
     * @return int The converted index value.
     */
    public function convertIndex(int $i): int
    {
        return $this->start + Util::normalizeIndex($i, \count($this), false) * $this->step;
    }

    /**
     * Return iterator to iterate slice range.
     *
     * @return \Generator<int, int>
     */
    public function getIterator(): \Generator
    {
        for ($i = 0; $i < \count($this); ++$i) {
            yield $this->convertIndex($i);
        }
    }
}

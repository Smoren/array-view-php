<?php

declare(strict_types=1);

namespace Smoren\ArrayView\Structs;

use Smoren\ArrayView\Util;

/**
 * Represents a normalized slice definition with start, end, and step values.
 *
 * @property-read int $start The start index of the normalized slice.
 * @property-read int $end The end index of the normalized slice.
 * @property-read int $step The step size for selecting elements in the normalized slice.
 *
 * @implements \IteratorAggregate<int, int>
 */
class NormalizedSlice extends Slice implements \Countable, \IteratorAggregate
{
    /**
     * @var int|null The start index of the normalized slice.
     */
    public ?int $start; // TODO int, not int|null, but phpstan do not like it.
    /**
     * @var int|null The end index of the normalized slice.
     */
    public ?int $end;
    /**
     * @var int|null The step size for selecting elements in the normalized slice.
     */
    public ?int $step;

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
     * Return size of the slice range.
     *
     * @return int Size of the slice range.
     */
    public function count(): int
    {
        return intval(ceil(abs((($this->end - $this->start) / $this->step))));
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

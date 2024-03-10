<?php

namespace Smoren\ArrayView\Structs;

use Smoren\ArrayView\Util;

/**
 * @property-read int $start
 * @property-read int $end
 * @property-read int $step
 *
 * @implements \IteratorAggregate<int, int>
 */
class NormalizedSlice extends Slice implements \Countable, \IteratorAggregate
{
    public ?int $start; // TODO int, not int|null
    public ?int $end;
    public ?int $step;

    public function count(): int
    {
        return intval(ceil(abs((($this->end - $this->start) / $this->step))));
    }

    public function convertIndex(int $i): int
    {
        return $this->start + Util::normalizeIndex($i, \count($this), false) * $this->step;
    }

    /**
     * @return \Generator<int, int>
     */
    public function getIterator(): \Generator
    {
        for ($i = 0; $i < \count($this); ++$i) {
            yield $this->convertIndex($i);
        }
    }
}

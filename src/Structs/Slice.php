<?php

declare(strict_types=1);

namespace Smoren\ArrayView\Structs;

use Smoren\ArrayView\Exceptions\IndexError;
use Smoren\ArrayView\Exceptions\ValueError;
use Smoren\ArrayView\Util;

/**
 * @property-read int|null $start
 * @property-read int|null $end
 * @property-read int|null $step
 */
class Slice
{
    /**
     * @var int|null
     */
    public ?int $start;
    /**
     * @var int|null
     */
    public ?int $end;
    /**
     * @var int|null
     */
    public ?int $step;

    /**
     * @param string|Slice|array<int> $s
     *
     * @return Slice
     */
    public static function toSlice($s): Slice
    {
        /** @var mixed $s */
        if ($s instanceof Slice) {
            return $s;
        }

        if (\is_array($s) && self::isSliceArray($s)) {
            return new Slice(...$s);
        }

        if (!self::isSliceString($s)) {
            $str = \is_scalar($s) ? "{$s}" : gettype($s);
            throw new ValueError("Invalid slice: \"{$str}\".");
        }

        /** @var string $s */
        $slice = self::parseSliceString($s);

        return new Slice(...$slice);
    }

    /**
     * @param mixed $s
     *
     * @return bool
     */
    public static function isSlice($s): bool
    {
        return ($s instanceof Slice) || static::isSliceString($s) || static::isSliceArray($s);
    }

    /**
     * @param mixed $s
     *
     * @return bool
     */
    public static function isSliceString($s): bool
    {
        if (!\is_string($s)) {
            return false;
        }

        if (\is_numeric($s)) {
            return false;
        }

        if (!\preg_match('/^-?[0-9]*:?-?[0-9]*:?-?[0-9]*$/', $s)) {
            return false;
        }

        $slice = self::parseSliceString($s);

        return !(\count($slice) < 1 || \count($slice) > 3);
    }

    /**
     * @param mixed $s
     *
     * @return bool
     */
    public static function isSliceArray($s): bool
    {
        if (!\is_array($s)) {
            return false;
        }

        if (\count($s) > 3) {
            return false;
        }

        foreach ($s as $key => $item) {
            if (\is_string($key)) {
                return false;
            }
            if ($item !== null && (!\is_numeric($item) || \is_float($item + 0))) {
                return false;
            }
        }

        return true;
    }

    /**
     * @param int|null $start
     * @param int|null $end
     * @param int|null $step
     */
    public function __construct(?int $start = null, ?int $end = null, ?int $step = null)
    {
        $this->start = $start;
        $this->end = $end;
        $this->step = $step;
    }

    /**
     * @param int $containerSize
     *
     * @return NormalizedSlice
     */
    public function normalize(int $containerSize): NormalizedSlice
    {
        $step = $this->step ?? 1;

        if ($step > 0) {
            return $this->normalizeWithPositiveStep($containerSize, $step);
        } elseif ($step < 0) {
            return $this->normalizeWithNegativeStep($containerSize, $step);
        }

        throw new IndexError("Step cannot be 0.");
    }

    /**
     * @return string
     */
    public function toString(): string
    {
        [$start, $end, $step] = [$this->start ?? '', $this->end ?? '', $this->step ?? ''];
        return "{$start}:{$end}:{$step}";
    }

    /**
     * @param string $s
     * @return array<int|null>
     */
    private static function parseSliceString(string $s): array
    {
        if ($s === '') {
            return [];
        }
        return array_map(fn($x) => trim($x) === '' ? null : \intval(trim($x)), \explode(':', $s));
    }

    /**
     * @param int $x
     * @param int $min
     * @param int $max
     * @return int
     */
    private function squeezeInBounds(int $x, int $min, int $max): int
    {
        return max($min, min($max, $x));
    }

    private function normalizeWithPositiveStep(int $containerSize, int $step): NormalizedSlice
    {
        $start = $this->start ?? 0;
        $end = $this->end ?? $containerSize;

        [$start, $end, $step] = [(int)\round($start), (int)\round($end), (int)\round($step)];

        $start = Util::normalizeIndex($start, $containerSize, false);
        $end = Util::normalizeIndex($end, $containerSize, false);

        if ($start >= $containerSize) {
            $start = $end = $containerSize - 1;
        }

        $start = $this->squeezeInBounds($start, 0, $containerSize - 1);
        $end = $this->squeezeInBounds($end, 0, $containerSize);

        if ($end < $start) {
            $end = $start;
        }

        return new NormalizedSlice($start, $end, $step);
    }

    private function normalizeWithNegativeStep(int $containerSize, int $step): NormalizedSlice
    {
        $start = $this->start ?? $containerSize - 1;
        $end = $this->end ?? -1;

        [$start, $end, $step] = [(int)\round($start), (int)\round($end), (int)\round($step)];

        $start = Util::normalizeIndex($start, $containerSize, false);

        if (!($this->end === null)) {
            $end = Util::normalizeIndex($end, $containerSize, false);
        }

        if ($start < 0) {
            $start = $end = 0;
        }

        $start = $this->squeezeInBounds($start, 0, $containerSize - 1);
        $end = $this->squeezeInBounds($end, -1, $containerSize);

        if ($end > $start) {
            $end = $start;
        }

        return new NormalizedSlice($start, $end, $step);
    }
}

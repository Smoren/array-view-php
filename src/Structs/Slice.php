<?php

declare(strict_types=1);

namespace Smoren\ArrayView\Structs;

use Smoren\ArrayView\Exceptions\IndexError;
use Smoren\ArrayView\Exceptions\ValueError;
use Smoren\ArrayView\Util;

/**
 * Represents a slice definition for selecting a range of elements.
 *
 * @property-read int|null $start The start index of the slice range.
 * @property-read int|null $end The end index of the slice range.
 * @property-read int|null $step The step size for selecting elements in the slice range.
 */
class Slice
{
    /**
     * @var int|null The start index of the slice range.
     */
    public ?int $start;
    /**
     * @var int|null The end index of the slice range.
     */
    public ?int $end;
    /**
     * @var int|null The step size for selecting elements in the slice range.
     */
    public ?int $step;

    /**
     * Converts a slice string or Slice object into a Slice instance.
     *
     * @param string|Slice|array<int> $s The slice string/array or Slice object to convert.
     *
     * @return Slice The converted Slice instance.
     *
     * @throws ValueError if the slice representation is invalid.
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
     * Checks if the provided value is a Slice instance or a valid slice string.
     *
     * @param mixed $s The value to check.
     *
     * @return bool True if the value is a Slice instance or a valid slice string, false otherwise.
     */
    public static function isSlice($s): bool
    {
        return ($s instanceof Slice) || static::isSliceString($s) || static::isSliceArray($s);
    }

    /**
     * Checks if the provided value is a valid slice string.
     *
     * @param mixed $s The value to check.
     *
     * @return bool True if the value is a valid slice string, false otherwise.
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
     * Checks if the provided value is a valid slice array.
     *
     * @param mixed $s The value to check.
     *
     * @return bool True if the value is a valid slice array, false otherwise.
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
     * Creates a new Slice instance with optional start, end, and step values.
     *
     * @param int|null $start The start index of the slice range.
     * @param int|null $end The end index of the slice range.
     * @param int|null $step The step size for selecting elements in the slice range.
     */
    public function __construct(?int $start = null, ?int $end = null, ?int $step = null)
    {
        $this->start = $start;
        $this->end = $end;
        $this->step = $step;
    }

    /**
     * Normalizes the slice parameters based on the container length.
     *
     * @param int $containerSize The length of the container or array.
     *
     * @return NormalizedSlice The normalized slice parameters.
     *
     * @throws IndexError if the step value is 0.
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
     * Returns the string representation of the Slice.
     *
     * @return string The string representation of the Slice.
     */
    public function toString(): string
    {
        [$start, $end, $step] = [$this->start ?? '', $this->end ?? '', $this->step ?? ''];
        return "{$start}:{$end}:{$step}";
    }

    /**
     * Parses a slice string into an array of start, end, and step values.
     *
     * @param string $s The slice string to parse.
     *
     * @return array<int|null> An array of parsed start, end, and step values.
     */
    private static function parseSliceString(string $s): array
    {
        if ($s === '') {
            return [];
        }
        return array_map(fn($x) => trim($x) === '' ? null : \intval(trim($x)), \explode(':', $s));
    }

    /**
     * Constrains a value within a given range.
     *
     * @param int $x The value to constrain.
     * @param int $min The minimum allowed value.
     * @param int $max The maximum allowed value.
     *
     * @return int The constrained value.
     */
    private function squeezeInBounds(int $x, int $min, int $max): int
    {
        return max($min, min($max, $x));
    }

    /**
     * Normalizes the slice parameters based on the container length (for positive step only).
     *
     * @param int $containerSize The length of the container or array.
     * @param int $step Step size.
     *
     * @return NormalizedSlice The normalized slice parameters.
     */
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

    /**
     * Normalizes the slice parameters based on the container length (for negative step only).
     *
     * @param int $containerSize The length of the container or array.
     * @param int $step Step size.
     *
     * @return NormalizedSlice The normalized slice parameters.
     */
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

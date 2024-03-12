<?php

declare(strict_types=1);

namespace Smoren\ArrayView\Tests\Unit\Utils;

use Smoren\ArrayView\Exceptions\IndexError;
use Smoren\ArrayView\Util;

class NormalizeIndexTest extends \Codeception\Test\Unit
{
    /**
     * @dataProvider dataProviderForNormalizeIndexSuccess
     */
    public function testNormalizeIndexSuccess(array $source, int $index, int $expected)
    {
        $normalizedIndex = Util::normalizeIndex($index, \count($source));
        $this->assertSame($expected, $source[$normalizedIndex]);
    }

    /**
     * @dataProvider dataProviderForNormalizeIndexNotThrow
     */
    public function testNormalizeIndexNotThrow(array $source, int $index, int $expected)
    {
        $normalizedIndex = Util::normalizeIndex($index, \count($source), false);
        $this->assertSame($expected, $normalizedIndex);
    }

    /**
     * @dataProvider dataProviderForNormalizeIndexError
     */
    public function testNormalizeIndexError(array $source, int $index)
    {
        $this->expectException(IndexError::class);
        $this->expectExceptionMessage("Index {$index} is out of range.");
        Util::normalizeIndex($index, \count($source));
    }

    public function dataProviderForNormalizeIndexSuccess(): array
    {
        return [
            [[1], 0, 1],
            [[1], -1, 1],
            [[1, 2], 0, 1],
            [[1, 2], -1, 2],
            [[1, 2], 1, 2],
            [[1, 2], -2, 1],
            [[1, 2, 3], 0, 1],
            [[1, 2, 3], -1, 3],
            [[1, 2, 3], 1, 2],
            [[1, 2, 3], -2, 2],
            [[1, 2, 3], 2, 3],
            [[1, 2, 3], -3, 1],
        ];
    }

    public function dataProviderForNormalizeIndexNotThrow(): array
    {
        return [
            [[1], 0, 0],
            [[1], -1, 0],
            [[1, 2], 0, 0],
            [[1, 2], -1, 1],
            [[1, 2], 1, 1],
            [[1, 2], -2, 0],
            [[1, 2, 3], 0, 0],
            [[1, 2, 3], -1, 2],
            [[1, 2, 3], 1, 1],
            [[1, 2, 3], -2, 1],
            [[1, 2, 3], 2, 2],
            [[1, 2, 3], -3, 0],

            [[], 0, 0],
            [[], -1, -1],
            [[], 2, 2],
            [[], -2, -2],
            [[1], 1, 1],
            [[1], -2, -1],
            [[1, 2], 2, 2],
            [[1, 2], -3, -1],
            [[1, 2, 3], 3, 3],
            [[1, 2, 3], -4, -1],
            [[1, 2, 3], 4, 4],
            [[1, 2, 3], -5, -2],
            [[1, 2, 3], 100, 100],
            [[1, 2, 3], -101, -98],
        ];
    }

    public function dataProviderForNormalizeIndexError(): array
    {
        return [
            [[], 0],
            [[], -1],
            [[], 2],
            [[], -2],
            [[1], 1],
            [[1], -2],
            [[1, 2], 2],
            [[1, 2], -3],
            [[1, 2, 3], 3],
            [[1, 2, 3], -4],
            [[1, 2, 3], 4],
            [[1, 2, 3], -5],
            [[1, 2, 3], 100],
            [[1, 2, 3], -101],
        ];
    }
}

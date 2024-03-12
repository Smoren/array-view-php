<?php

declare(strict_types=1);

namespace Smoren\ArrayView\Tests\Unit\Structs;

use Smoren\ArrayView\Structs\Slice;

class NormalizedSliceTest extends \Codeception\Test\Unit
{
    /**
     * @dataProvider dataProviderForToSlice
     */
    public function testToSlice($input, $containerSize, array $expected)
    {
        $actual = Slice::toSlice($input)->normalize($containerSize);
        $expectedSlice = new Slice(...$expected);

        $this->assertSame($expectedSlice->getStart(), $actual->getStart());
        $this->assertSame($expectedSlice->getEnd(), $actual->getEnd());
        $this->assertSame($expectedSlice->getStep(), $actual->getStep());
    }

    public function dataProviderForToSlice(): array
    {
        return [
            [':', 0, [0, 0, 1]],
            ['::', 0, [0, 0, 1]],
            ['::', 1, [0, 1, 1]],
            ['::', 2, [0, 2, 1]],
            ['0:', 0, [0, 0, 1]],
            ['0:', 1, [0, 1, 1]],
            ['1:', 0, [0, 0, 1]],
            ['-1:', 0, [0, 0, 1]],
            ['-1:', 1, [0, 1, 1]],
            ['0:0:', 0, [0, 0, 1]],
            ['0:0:', 10, [0, 0, 1]],
            ['1:1:', 0, [0, 0, 1]],
            ['1:1:', 10, [1, 1, 1]],
            ['-1:-1:', 0, [0, 0, 1]],
            ['-1:-1:', 10, [9, 9, 1]],
            ['1:1:-1', 0, [0, 0, -1]],
            ['1:1:-1', 1, [0, 0, -1]],
            ['1:1:-1', 2, [1, 1, -1]],
            ['-1:-1:-1', 0, [0, 0, -1]],
            ['-1:-1:-1', 1, [0, 0, -1]],
            ['-1:-1:-1', 2, [1, 1, -1]],
            ['1:2:3', 0, [0, 0, 3]],
            ['1:2:3', 1, [0, 0, 3]],
            ['1:2:3', 2, [1, 2, 3]],
            ['1:2:3', 3, [1, 2, 3]],
            ['1:2:3', 10, [1, 2, 3]],
        ];
    }
}

<?php

namespace Smoren\ArrayView\Tests\Unit\ArraySliceView;

use Smoren\ArrayView\Selectors\SliceSelector;
use Smoren\ArrayView\Views\ArrayView;

class WriteTest extends \Codeception\Test\Unit
{
    /**
     * @dataProvider dataProviderForSliceSubviewWrite
     */
    public function testWriteBySliceIndex(array $source, string $config, array $toWrite, array $expected)
    {
        $view = ArrayView::toView($source);

        $view[$config] = $toWrite;

        $this->assertSame($expected, [...$view]);
        $this->assertSame($expected, $source);
    }

    /**
     * @dataProvider dataProviderForSliceSubviewWrite
     * @dataProvider dataProviderForSliceArraySubviewWrite
     */
    public function testWriteBySubview(array $source, $config, array $toWrite, array $expected)
    {
        $view = ArrayView::toView($source);

        $view->subview(new SliceSelector($config))[':'] = $toWrite;

        $this->assertSame($expected, [...$view]);
        $this->assertSame($expected, $source);
    }

    public function dataProviderForSliceSubviewWrite(): array
    {
        return [
            [[], ':', [], []],
            [[1], ':', [11], [11]],
            [[1, 2, 3], ':', [2, 4, 6], [2, 4, 6]],
            [[1, 2, 3], '0:', [2, 4, 6], [2, 4, 6]],
            [[1, 2, 3], ':3', [2, 4, 6], [2, 4, 6]],
            [[1, 2, 3], '0:3', [2, 4, 6], [2, 4, 6]],
            [[1, 2, 3], '1:', [22, 33], [1, 22, 33]],
            [[1, 2, 3], ':2', [11, 22], [11, 22, 3]],
            [[1, 2, 3], ':-1', [11, 22], [11, 22, 3]],
            [[1, 2, 3, 4, 5, 6], '::2', [77, 88, 99], [77, 2, 88, 4, 99, 6]],
            [[1, 2, 3, 4, 5, 6], '::-2', [77, 88, 99], [1, 99, 3, 88, 5, 77]],
            [[1, 2, 3, 4, 5, 6], '1::2', [77, 88, 99], [1, 77, 3, 88, 5, 99]],
            [[1, 2, 3, 4, 5, 6], '-2::-2', [77, 88, 99], [99, 2, 88, 4, 77, 6]],
            [[1, 2, 3, 4, 5, 6, 7, 8], ':-2:2', [77, 88, 99], [77, 2, 88, 4, 99, 6, 7, 8]],
            [[1, 2, 3, 4, 5, 6, 7, 8], ':6:2', [77, 88, 99], [77, 2, 88, 4, 99, 6, 7, 8]],
            [[1, 2, 3, 4, 5, 6, 7, 8], '1:-1:2', [77, 88, 99], [1, 77, 3, 88, 5, 99, 7, 8]],
        ];
    }

    public function dataProviderForSliceArraySubviewWrite(): array
    {
        return [
            [[], [], [], []],
            [[], [null, null], [], []],
            [[1], [null, null], [11], [11]],
            [[1, 2, 3], [null, null], [2, 4, 6], [2, 4, 6]],
            [[1, 2, 3], [0,], [2, 4, 6], [2, 4, 6]],
            [[1, 2, 3], [0, 3], [2, 4, 6], [2, 4, 6]],
            [[1, 2, 3], [0, 3], [2, 4, 6], [2, 4, 6]],
            [[1, 2, 3], [1,], [22, 33], [1, 22, 33]],
            [[1, 2, 3], [null, 2], [11, 22], [11, 22, 3]],
            [[1, 2, 3], [null, -1], [11, 22], [11, 22, 3]],
            [[1, 2, 3, 4, 5, 6], [null, null, 2], [77, 88, 99], [77, 2, 88, 4, 99, 6]],
            [[1, 2, 3, 4, 5, 6], [null, null, -2], [77, 88, 99], [1, 99, 3, 88, 5, 77]],
            [[1, 2, 3, 4, 5, 6], [1, null, 2], [77, 88, 99], [1, 77, 3, 88, 5, 99]],
            [[1, 2, 3, 4, 5, 6], [-2, null, -2], [77, 88, 99], [99, 2, 88, 4, 77, 6]],
            [[1, 2, 3, 4, 5, 6, 7, 8], [null, -2, 2], [77, 88, 99], [77, 2, 88, 4, 99, 6, 7, 8]],
            [[1, 2, 3, 4, 5, 6, 7, 8], [null, 6, 2], [77, 88, 99], [77, 2, 88, 4, 99, 6, 7, 8]],
            [[1, 2, 3, 4, 5, 6, 7, 8], [1, -1, 2], [77, 88, 99], [1, 77, 3, 88, 5, 99, 7, 8]],
        ];
    }
}

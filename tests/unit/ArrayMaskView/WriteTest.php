<?php

namespace Smoren\ArrayView\Tests\Unit\ArrayMaskView;

use Smoren\ArrayView\Selectors\MaskSelector;
use Smoren\ArrayView\Views\ArrayView;

class WriteTest extends \Codeception\Test\Unit
{
    /**
     * @dataProvider dataProviderForMaskSubviewWrite
     */
    public function testWriteByIndex(array $source, array $config, array $toWrite, array $expected)
    {
        $view = ArrayView::toView($source);

        $view[new MaskSelector($config)] = $toWrite;

        $this->assertSame($expected, [...$view]);
        $this->assertSame($expected, $source);
    }

    /**
     * @dataProvider dataProviderForMaskSubviewWrite
     */
    public function testWriteBySubview(array $source, $config, array $toWrite, array $expected)
    {
        $view = ArrayView::toView($source);

        $view->subview(new MaskSelector($config))[':'] = $toWrite;

        $this->assertSame($expected, [...$view]);
        $this->assertSame($expected, $source);
    }

    public function dataProviderForMaskSubviewWrite(): array
    {
        return [
            [
                [],
                [],
                [],
                [],
            ],
            [
                [1],
                [0],
                [],
                [1],
            ],
            [
                [1, 2, 3],
                [0, 0, 0],
                [],
                [1, 2, 3],
            ],
            [
                [1],
                [1],
                [2],
                [2],
            ],
            [
                [1, 2],
                [1, 0],
                [2],
                [2, 2],
            ],
            [
                [1, 2],
                [0, 1],
                [3],
                [1, 3],
            ],
            [
                [1, 2],
                [1, 1],
                [2, 3],
                [2, 3],
            ],
            [
                [1, 2, 3, 4, 5, 6, 7, 8, 9],
                [0, 1, 0, 1, 0, 1, 0, 1, 0],
                [3, 5, 7, 9],
                [1, 3, 3, 5, 5, 7, 7, 9, 9],
            ],
            [
                [1, 2, 3, 4, 5, 6, 7, 8, 9],
                [1, 1, 1, 0, 0, 0, 0, 0, 1],
                [2, 3, 4, 10],
                [2, 3, 4, 4, 5, 6, 7, 8, 10],
            ],
        ];
    }
}

<?php

namespace Smoren\ArrayView\Tests\Unit\ArrayMaskView;

use Smoren\ArrayView\Selectors\MaskSelector;
use Smoren\ArrayView\Views\ArrayView;

class WriteTest extends \Codeception\Test\Unit
{
    /**
     * @dataProvider dataProviderForMaskSubviewWrite
     */
    public function testWriteByIndex(array $source, array $mask, array $toWrite, array $expected)
    {
        $view = ArrayView::toView($source);

        $view[new MaskSelector($mask)] = $toWrite;

        $this->assertSame($expected, [...$view]);
        $this->assertSame($expected, $source);
    }

    /**
     * @dataProvider dataProviderForMaskSubviewWrite
     */
    public function testWriteByArrayIndex(array $source, array $mask, array $toWrite, array $expected)
    {
        $view = ArrayView::toView($source);

        $view[$mask] = $toWrite;

        $this->assertSame($expected, [...$view]);
        $this->assertSame($expected, $source);
    }

    /**
     * @dataProvider dataProviderForMaskSubviewWrite
     */
    public function testWriteByArrayViewIndex(array $source, array $mask, array $toWrite, array $expected)
    {
        $view = ArrayView::toView($source);

        $view[ArrayView::toView($mask)] = $toWrite;

        $this->assertSame($expected, [...$view]);
        $this->assertSame($expected, $source);
    }

    /**
     * @dataProvider dataProviderForMaskSubviewWrite
     */
    public function testWriteBySubview(array $source, array $mask, array $toWrite, array $expected)
    {
        $view = ArrayView::toView($source);

        $view->subview(new MaskSelector($mask))[':'] = $toWrite;

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
                [false],
                [],
                [1],
            ],
            [
                [1, 2, 3],
                [false, false, false],
                [],
                [1, 2, 3],
            ],
            [
                [1],
                [true],
                [2],
                [2],
            ],
            [
                [1, 2],
                [true, false],
                [2],
                [2, 2],
            ],
            [
                [1, 2],
                [false, true],
                [3],
                [1, 3],
            ],
            [
                [1, 2],
                [true, true],
                [2, 3],
                [2, 3],
            ],
            [
                [1, 2, 3, 4, 5, 6, 7, 8, 9],
                [false, true, false, true, false, true, false, true, false],
                [3, 5, 7, 9],
                [1, 3, 3, 5, 5, 7, 7, 9, 9],
            ],
            [
                [1, 2, 3, 4, 5, 6, 7, 8, 9],
                [true, true, true, false, false, false, false, false, true],
                [2, 3, 4, 10],
                [2, 3, 4, 4, 5, 6, 7, 8, 10],
            ],
        ];
    }
}

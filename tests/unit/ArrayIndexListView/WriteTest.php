<?php

namespace Smoren\ArrayView\Tests\Unit\ArrayIndexListView;

use Smoren\ArrayView\Selectors\IndexListSelector;
use Smoren\ArrayView\Views\ArrayView;

class WriteTest extends \Codeception\Test\Unit
{
    /**
     * @dataProvider dataProviderForMaskSubviewWrite
     */
    public function testWriteByIndex(array $source, array $indexes, array $toWrite, array $expected)
    {
        $view = ArrayView::toView($source);

        $view[new IndexListSelector($indexes)] = $toWrite;

        $this->assertSame($expected, [...$view]);
        $this->assertSame($expected, $source);
    }

    /**
     * @dataProvider dataProviderForMaskSubviewWrite
     */
    public function testWriteByArrayIndex(array $source, array $indexes, array $toWrite, array $expected)
    {
        $view = ArrayView::toView($source);

        $view[$indexes] = $toWrite;

        $this->assertSame($expected, [...$view]);
        $this->assertSame($expected, $source);
    }

    /**
     * @dataProvider dataProviderForMaskSubviewWrite
     */
    public function testWriteByArrayViewIndex(array $source, array $indexes, array $toWrite, array $expected)
    {
        $view = ArrayView::toView($source);

        $view[ArrayView::toView($indexes)] = $toWrite;

        $this->assertSame($expected, [...$view]);
        $this->assertSame($expected, $source);
    }

    /**
     * @dataProvider dataProviderForMaskSubviewWrite
     */
    public function testWriteBySubview(array $source, $config, array $toWrite, array $expected)
    {
        $view = ArrayView::toView($source);

        $view->subview(new IndexListSelector($config))[':'] = $toWrite;

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
                [],
                [],
                [1],
            ],
            [
                [1, 2, 3],
                [],
                [],
                [1, 2, 3],
            ],
            [
                [1],
                [0],
                [2],
                [2],
            ],
            [
                [1],
                [0, 0],
                [3, 3],
                [3],
            ],
            [
                [1],
                [0, 0, 0],
                [4, 4, 4],
                [4],
            ],
            [
                [1, 2],
                [0],
                [2],
                [2, 2],
            ],
            [
                [1, 2],
                [1],
                [3],
                [1, 3],
            ],
            [
                [1, 2],
                [0, 1],
                [2, 3],
                [2, 3],
            ],
            [
                [1, 2, 3, 4, 5, 6, 7, 8, 9],
                [1, 3, 5, 7],
                [3, 5, 7, 9],
                [1, 3, 3, 5, 5, 7, 7, 9, 9],
            ],
            [
                [1, 2, 3, 4, 5, 6, 7, 8, 9],
                [7, 5, 3, 1],
                [9, 7, 5, 3],
                [1, 3, 3, 5, 5, 7, 7, 9, 9],
            ],
            [
                [1, 2, 3, 4, 5, 6, 7, 8, 9],
                [1, 5, 3, 7],
                [3, 7, 5, 9],
                [1, 3, 3, 5, 5, 7, 7, 9, 9],
            ],
            [
                [1, 2, 3, 4, 5, 6, 7, 8, 9],
                [0, 1, 7, 8],
                [2, 3, 9, 10],
                [2, 3, 3, 4, 5, 6, 7, 9, 10],
            ],
            [
                [1, 2, 3, 4, 5, 6, 7, 8, 9],
                [1, 1, 5, 5, 3],
                [4, 4, 8, 8, 5],
                [1, 4, 3, 5, 5, 8, 7, 8, 9],
            ],
        ];
    }
}

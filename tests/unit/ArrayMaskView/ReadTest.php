<?php

namespace Smoren\ArrayView\Tests\Unit\ArrayMaskView;

use Smoren\ArrayView\Selectors\MaskSelector;
use Smoren\ArrayView\Views\ArrayMaskView;
use Smoren\ArrayView\Views\ArrayView;

class ReadTest extends \Codeception\Test\Unit
{
    /**
     * @dataProvider dataProviderForRead
     */
    public function testReadByMethod(array $source, array $mask, array $expected)
    {
        $view = ArrayView::toView($source);

        $selector = new MaskSelector($mask);
        $this->assertSame($mask, $selector->getValue());

        $subview = $view->subview($selector);
        $this->assertInstanceOf(ArrayMaskView::class, $subview);

        $this->assertSame($expected, [...$subview]);
        $this->assertSame(\count($expected), \count($subview));

        for ($i = 0; $i < \count($subview); ++$i) {
            $this->assertSame($expected[$i], $subview[$i]);
        }

        for ($i = 0; $i < \count($view); ++$i) {
            $this->assertSame($source[$i], $view[$i]);
        }

        $this->assertSame($source, $view->toArray());
        $this->assertSame($expected, $subview->toArray());

        $this->assertSame($source, [...$view]);
        $this->assertSame($expected, [...$subview]);
    }

    /**
     * @dataProvider dataProviderForRead
     */
    public function testReadByIndex(array $source, array $mask, array $expected)
    {
        $view = ArrayView::toView($source);
        $subArray = $view[new MaskSelector($mask)];

        $this->assertSame($expected, $subArray);
        $this->assertSame(\count($expected), \count($subArray));

        for ($i = 0; $i < \count($subArray); ++$i) {
            $this->assertSame($expected[$i], $subArray[$i]);
        }

        for ($i = 0; $i < \count($view); ++$i) {
            $this->assertSame($source[$i], $view[$i]);
        }

        $this->assertSame($source, $view->toArray());
        $this->assertSame($source, [...$view]);
        $this->assertSame($expected, $subArray);
    }

    /**
     * @dataProvider dataProviderForRead
     */
    public function testReadByArrayViewIndex(array $source, array $mask, array $expected)
    {
        $view = ArrayView::toView($source);
        $subArray = $view[ArrayView::toView($mask)];

        $this->assertSame($expected, $subArray);
        $this->assertSame(\count($expected), \count($subArray));

        for ($i = 0; $i < \count($subArray); ++$i) {
            $this->assertSame($expected[$i], $subArray[$i]);
        }

        for ($i = 0; $i < \count($view); ++$i) {
            $this->assertSame($source[$i], $view[$i]);
        }

        $this->assertSame($source, $view->toArray());
        $this->assertSame($source, [...$view]);
        $this->assertSame($expected, $subArray);
    }

    /**
     * @dataProvider dataProviderForRead
     */
    public function testReadByArrayIndex(array $source, array $mask, array $expected)
    {
        $view = ArrayView::toView($source);
        $subArray = $view[$mask];

        $this->assertSame($expected, $subArray);
        $this->assertSame(\count($expected), \count($subArray));

        for ($i = 0; $i < \count($subArray); ++$i) {
            $this->assertSame($expected[$i], $subArray[$i]);
        }

        for ($i = 0; $i < \count($view); ++$i) {
            $this->assertSame($source[$i], $view[$i]);
        }

        $this->assertSame($source, $view->toArray());
        $this->assertSame($source, [...$view]);
        $this->assertSame($expected, $subArray);
    }

    public function dataProviderForRead(): array
    {
        return [
            [
                [],
                [],
                [],
            ],
            [
                [1],
                [false],
                [],
            ],
            [
                [1, 2, 3],
                [false, false, false],
                [],
            ],
            [
                [1],
                [true],
                [1],
            ],
            [
                [1, 2],
                [true, false],
                [1],
            ],
            [
                [1, 2],
                [false, true],
                [2],
            ],
            [
                [1, 2],
                [true, true],
                [1, 2],
            ],
            [
                [1, 2, 3, 4, 5, 6, 7, 8, 9],
                [false, true, false, true, false, true, false, true, false],
                [2, 4, 6, 8],
            ],
            [
                [1, 2, 3, 4, 5, 6, 7, 8, 9],
                [true, true, true, false, false, false, false, false, true],
                [1, 2, 3, 9],
            ],
        ];
    }
}

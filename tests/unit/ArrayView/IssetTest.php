<?php

namespace Smoren\ArrayView\Tests\Unit\ArrayView;

use Smoren\ArrayView\Selectors\IndexListSelector;
use Smoren\ArrayView\Selectors\MaskSelector;
use Smoren\ArrayView\Selectors\PipeSelector;
use Smoren\ArrayView\Views\ArrayView;

class IssetTest extends \Codeception\Test\Unit
{
    /**
     * @dataProvider dataProviderForIssetSelectorFalse
     */
    public function testIssetSelectorFalse(array $source, $slice)
    {
        $view = ArrayView::toView($source);
        $this->assertFalse(isset($view[$slice]));
    }

    /**
     * @dataProvider dataProviderForIssetPipeSelectorTrue
     */
    public function testIssetPipeSelectorTrue(array $source, array $selectors)
    {
        $view = ArrayView::toView($source);
        $this->assertTrue(isset($view[new PipeSelector($selectors)]));
    }

    /**
     * @dataProvider dataProviderForIssetPipeSelectorFalse
     */
    public function testIssetPipeSelectorFalse(array $source, array $selectors)
    {
        $view = ArrayView::toView($source);
        $this->assertFalse(isset($view[new PipeSelector($selectors)]));
    }

    public function dataProviderForIssetSelectorFalse(): array
    {
        return [
            [[1, 2, 3, 4, 5, 6, 7, 8, 9], null],
            [[1, 2, 3, 4, 5, 6, 7, 8, 9], true],
            [[1, 2, 3, 4, 5, 6, 7, 8, 9], false],
            [[1, 2, 3, 4, 5, 6, 7, 8, 9], 1.1],
            [[1, 2, 3, 4, 5, 6, 7, 8, 9], INF],
            [[1, 2, 3, 4, 5, 6, 7, 8, 9], -INF],
            [[1, 2, 3, 4, 5, 6, 7, 8, 9], [1,66]],
            [[1, 2, 3, 4, 5, 6, 7, 8, 9], 'asd'],
            [[1, 2, 3, 4, 5, 6, 7, 8, 9], ['a' => 1]],
            [[1, 2, 3, 4, 5, 6, 7, 8, 9], new \ArrayObject(['a' => 1])],
        ];
    }

    public function dataProviderForIssetPipeSelectorTrue(): array
    {
        return [
            [
                [1, 2, 3, 4, 5],
                [
                    new MaskSelector([true, false, true, false, true]),
                ],
            ],
            [
                [1, 2, 3, 4, 5, 6, 7, 8, 9],
                [
                    new IndexListSelector([0, 1, 2]),
                    new MaskSelector([true, false, true]),
                ],
            ],
            [
                [1, 2, 3, 4, 5, 6, 7, 8, 9],
                [
                    new IndexListSelector([0, 1, 2]),
                    new MaskSelector([true, false, true]),
                    new IndexListSelector([0, 1]),
                ],
            ],
            [
                [1, 2, 3, 4, 5, 6, 7, 8, 9],
                [
                    new IndexListSelector([0, 1, 2]),
                    new MaskSelector([true, false, true]),
                    new IndexListSelector([-2]),
                ],
            ],
            [
                [1, 2, 3, 4, 5, 6, 7, 8, 9],
                [
                    new IndexListSelector([0, 1, 2]),
                    new PipeSelector([
                        new MaskSelector([true, false, true]),
                        new IndexListSelector([-2]),
                    ]),
                ],
            ],
        ];
    }

    public function dataProviderForIssetPipeSelectorFalse(): array
    {
        return [
            [
                [1, 2, 3, 4, 5, 6, 7, 8, 9],
                [
                    new MaskSelector([]),
                ],
            ],
            [
                [1, 2, 3, 4, 5, 6, 7, 8, 9],
                [
                    new IndexListSelector([0, 1, 2]),
                    new MaskSelector([true, false]),
                ],
            ],
            [
                [1, 2, 3, 4, 5, 6, 7, 8, 9],
                [
                    new IndexListSelector([0, 1, 2]),
                    new MaskSelector([true, false, true]),
                    new IndexListSelector([0, 2]),
                ],
            ],
            [
                [1, 2, 3, 4, 5, 6, 7, 8, 9],
                [
                    new IndexListSelector([0, 1, 2]),
                    new MaskSelector([true, false, true]),
                    new IndexListSelector([-3]),
                ],
            ],
            [
                [1, 2, 3, 4, 5, 6, 7, 8, 9],
                [
                    new IndexListSelector([0, 1, 2]),
                    new PipeSelector([
                        new MaskSelector([true, false, true]),
                        new IndexListSelector([-3]),
                    ]),
                ],
            ],
        ];
    }
}

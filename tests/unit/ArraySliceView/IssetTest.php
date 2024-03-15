<?php

namespace Smoren\ArrayView\Tests\Unit\ArraySliceView;

use Smoren\ArrayView\Selectors\SliceSelector;
use Smoren\ArrayView\Views\ArrayView;

class IssetTest extends \Codeception\Test\Unit
{
    /**
     * @dataProvider dataProviderForIssetSelectorStringTrue
     * @dataProvider dataProviderForIssetSelectorArrayTrue
     */
    public function testIssetSelectorObjectTrue(array $source, $slice)
    {
        $view = ArrayView::toView($source);
        $this->assertTrue(isset($view[new SliceSelector($slice)]));
    }

    /**
     * @dataProvider dataProviderForIssetSelectorStringTrue
     */
    public function testIssetSelectorStringTrue(array $source, string $slice)
    {
        $view = ArrayView::toView($source);
        $this->assertTrue(isset($view[$slice]));
    }

    public function dataProviderForIssetSelectorStringTrue(): array
    {
        return [
            [[1, 2, 3, 4, 5, 6, 7, 8, 9], '1:6'],
            [[1, 2, 3, 4, 5, 6, 7, 8, 9], '1:6:1'],
            [[1, 2, 3, 4, 5, 6, 7, 8, 9], '1:6:2'],
            [[1, 2, 3, 4, 5, 6, 7, 8, 9], '2:8'],
            [[1, 2, 3, 4, 5, 6, 7, 8, 9], '2:8:1'],
            [[1, 2, 3, 4, 5, 6, 7, 8, 9], '2:8:2'],

            [[1, 2, 3, 4, 5, 6, 7, 8, 9], '-1::-1'],
            [[1, 2, 3, 4, 5, 6, 7, 8, 9], '-1:0:-1'],

            [[1, 2, 3, 4, 5, 6, 7, 8, 9], '0:9:1'],
            [[1, 2, 3, 4, 5, 6, 7, 8, 9], '0:9:2'],
            [[1, 2, 3, 4, 5, 6, 7, 8, 9], '1:9:2'],
            [[1, 2, 3, 4, 5, 6, 7, 8, 9], '0:10:1'],
            [[1, 2, 3, 4, 5, 6, 7, 8, 9], '0:10:2'],
            [[1, 2, 3, 4, 5, 6, 7, 8, 9], '-9:9:1'],
            [[1, 2, 3, 4, 5, 6, 7, 8, 9], '-9:9:2'],
            [[1, 2, 3, 4, 5, 6, 7, 8, 9], '-10:10:1'],
            [[1, 2, 3, 4, 5, 6, 7, 8, 9], '-10:10:2'],
            [[1, 2, 3, 4, 5, 6, 7, 8, 9], '-5:10:1'],
            [[1, 2, 3, 4, 5, 6, 7, 8, 9], '-5:100:2'],

            [[], '0:'],
            [[], '0:0'],
            [[], '0:0:1'],
            [[], '1:-1'],
            [[], '-1:-1'],
            [[], '-2:-1'],
            [[], '-2:-1:2'],
            [[], '-1:0:-1'],
            [[1], '0:'],
            [[1], '0:1'],
            [[1], '0:1:1'],
            [[1], '0:1:2'],
            [[1], '0:-1'],
            [[1], '0:-1:1'],
            [[1], '0:-1:2'],
            [[1], '0:10:100'],
            [[1], '1:10:100'],
            [[1], '0:'],
            [[1, 2, 3], '0:0:1'],
            [[1], '1:'],
            [[1, 2], '1:0'],
            [[1, 2], '1::-1'],
            [[1, 2], '0:1'],
            [[1, 2], '1:1'],
            [[1, 2, 3, 4, 5, 6, 7, 8, 9], '1::2'],
            [[1, 2, 3, 4, 5, 6, 7, 8, 9], '::2'],
        ];
    }

    public function dataProviderForIssetSelectorArrayTrue(): array
    {
        return [
            [[1, 2, 3, 4, 5, 6, 7, 8, 9], [1,6]],
            [[1, 2, 3, 4, 5, 6, 7, 8, 9], [1,6,1]],
            [[1, 2, 3, 4, 5, 6, 7, 8, 9], [1,6,2]],
            [[1, 2, 3, 4, 5, 6, 7, 8, 9], [2,8]],
            [[1, 2, 3, 4, 5, 6, 7, 8, 9], [2,8,1]],
            [[1, 2, 3, 4, 5, 6, 7, 8, 9], [2,8,2]],
        ];
    }
}

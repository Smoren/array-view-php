<?php

namespace Smoren\ArrayView\Tests\Unit\ArrayView;

use Smoren\ArrayView\Selectors\SliceSelector;
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

    public function dataProviderForIssetSelectorFalse(): array
    {
        return [
            [[1, 2, 3, 4, 5, 6, 7, 8, 9], null],
            [[1, 2, 3, 4, 5, 6, 7, 8, 9], true],
            [[1, 2, 3, 4, 5, 6, 7, 8, 9], false],
            [[1, 2, 3, 4, 5, 6, 7, 8, 9], 1.1],
            [[1, 2, 3, 4, 5, 6, 7, 8, 9], INF],
            [[1, 2, 3, 4, 5, 6, 7, 8, 9], -INF],
            [[1, 2, 3, 4, 5, 6, 7, 8, 9], [1,6]],
            [[1, 2, 3, 4, 5, 6, 7, 8, 9], 'asd'],
            [[1, 2, 3, 4, 5, 6, 7, 8, 9], ['a' => 1]],
            [[1, 2, 3, 4, 5, 6, 7, 8, 9], new \ArrayObject(['a' => 1])],
        ];
    }
}

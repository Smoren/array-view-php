<?php

namespace Smoren\ArrayView\Tests\Unit\ArrayMaskView;

use Smoren\ArrayView\Exceptions\SizeError;
use Smoren\ArrayView\Selectors\MaskSelector;
use Smoren\ArrayView\Views\ArrayView;

class IssetTest extends \Codeception\Test\Unit
{

    /**
     * @dataProvider dataProviderForIssetSelectorTrue
     */
    public function testIssetSelectorTrue(array $source, array $boolMask)
    {
        $view = ArrayView::toView($source);

        $this->assertTrue(isset($view[new MaskSelector($boolMask)]));
    }

    /**
     * @dataProvider dataProviderForIssetSelectorFalse
     */
    public function testIssetSelectorFalse(array $source, array $indexes)
    {
        $view = ArrayView::toView($source);

        $this->assertFalse(isset($view[new MaskSelector($indexes)]));

        $this->expectException(SizeError::class);
        $_ = $view[new MaskSelector($indexes)];
    }

    public function dataProviderForIssetSelectorTrue(): array
    {
        return [
            [[], [], []],
            [[1], [0], []],
            [[1, 2, 3], [0, 0, 0], []],
            [[1], [1], [1]],
            [[1, 2], [1, 0], [1]],
            [[1, 2], [0, 1], [2]],
            [[1, 2], [1, 1], [1, 2]],
            [[1, 2, 3, 4, 5, 6, 7, 8, 9], [0, 1, 0, 1, 0, 1, 0, 1, 0], [2, 4, 6, 8]],
            [[1, 2, 3, 4, 5, 6, 7, 8, 9], [1, 1, 1, 0, 0, 0, 0, 0, 1], [1, 2, 3, 9]],
        ];
    }

    public function dataProviderForIssetSelectorFalse(): array
    {
        return [
            [[], [0], []],
            [[], [1], []],
            [[], [0, 1], []],
            [[1], [], []],
            [[1], [0, 0], []],
            [[1], [1, 0], []],
            [[1], [1, 1, 1], []],
            [[1, 2, 3], [], []],
            [[1, 2, 3], [0], []],
            [[1, 2, 3], [0, 0], []],
            [[1, 2, 3], [0, 0, 0, 0], []],
            [[1, 2, 3], [0, 0, 0, 0, 0], []],
            [[1, 2, 3], [1], []],
            [[1, 2, 3], [1, 1], []],
            [[1, 2, 3], [1, 1, 1, 1], []],
            [[1, 2, 3], [1, 1, 1, 1, 1], []],
            [[1, 2, 3, 4, 5, 6, 7, 8, 9], [0, 1, 0, 1, 0, 1, 0, 1], [2, 4, 6, 8]],
            [[1, 2, 3, 4, 5, 6, 7, 8, 9], [1, 1, 1, 0, 0, 0, 0, 0, 1, 0], [1, 2, 3, 9]],
        ];
    }
}

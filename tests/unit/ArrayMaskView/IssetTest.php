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
        $this->assertTrue(isset($view[$boolMask]));
        $this->assertTrue(isset($view[ArrayView::toView($boolMask)]));
    }

    /**
     * @dataProvider dataProviderForIssetSelectorFalse
     */
    public function testIssetSelectorFalse(array $source, array $boolMask)
    {
        $view = ArrayView::toView($source);

        $this->assertFalse(isset($view[new MaskSelector($boolMask)]));
        $this->assertFalse(isset($view[$boolMask]));
        $this->assertFalse(isset($view[ArrayView::toView($boolMask)]));

        $this->expectException(SizeError::class);
        $_ = $view[new MaskSelector($boolMask)];
    }

    public function dataProviderForIssetSelectorTrue(): array
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

    public function dataProviderForIssetSelectorFalse(): array
    {
        return [
            [
                [],
                [false],
                [],
            ],
            [
                [],
                [true],
                [],
            ],
            [
                [],
                [false, true],
                [],
            ],
            [
                [1],
                [false, false],
                [],
            ],
            [
                [1],
                [true, false],
                [],
            ],
            [
                [1],
                [true, true, true],
                [],
            ],
            [
                [1, 2, 3],
                [false],
                [],
            ],
            [
                [1, 2, 3],
                [false, false],
                [],
            ],
            [
                [1, 2, 3],
                [false, false, false, false],
                [],
            ],
            [
                [1, 2, 3],
                [false, false, false, false, false],
                [],
            ],
            [
                [1, 2, 3],
                [true],
                [],
            ],
            [
                [1, 2, 3],
                [true, true],
                [],
            ],
            [
                [1, 2, 3],
                [true, true, true, true],
                [],
            ],
            [
                [1, 2, 3],
                [true, true, true, true, true],
                [],
            ],
            [
                [1, 2, 3, 4, 5, 6, 7, 8, 9],
                [false, true, false, true, false, true, false, true],
                [2, 4, 6, 8],
            ],
            [
                [1, 2, 3, 4, 5, 6, 7, 8, 9],
                [true, true, true, false, false, false, false, false, true, false],
                [1, 2, 3, 9],
            ],
        ];
    }
}

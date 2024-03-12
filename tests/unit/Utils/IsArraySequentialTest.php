<?php

declare(strict_types=1);

namespace Smoren\ArrayView\Tests\Unit\Utils;

use Smoren\ArrayView\Exceptions\IndexError;
use Smoren\ArrayView\Util;

class IsArraySequentialTest extends \Codeception\Test\Unit
{
    /**
     * @dataProvider dataProviderForIsSequentialTrue
     */
    public function testIsSequentialTrue(array $source)
    {
        $this->assertTrue(Util::isArraySequential($source));
        $this->assertTrue(Util::isArraySequential($source, true));
    }

    /**
     * @dataProvider dataProviderForIsSequentialFalse
     */
    public function testIsSequentialFalse(array $source)
    {
        $this->assertFalse(Util::isArraySequential($source));
        $this->assertFalse(Util::isArraySequential($source, true));
    }

    public function dataProviderForIsSequentialTrue(): array
    {
        return [
            [[]],
            [['']],
            [[null]],
            [[1]],
            [['1']],
            [['test']],
            [[1, 2, 3]],
            [[0 => 1, 1 => 2, 2 => 3]],
            [['0' => 1, 1 => 2, 2 => 3]],
            [['0' => 1, '1' => 2, '2' => 3]],
            [[10, '20', 30.5]],
        ];
    }

    public function dataProviderForIsSequentialFalse(): array
    {
        return [
            [['a' => 1]],
            [[1 => 1]],
            [[0 => 1, 1 => 2, 3 => 3]],
            [[0 => 1, 1 => 2, 2 => 3, 'test' => 123]],
            [[1 => 2, 2 => 3]],
            [[1 => 2, 3 => 3, 'a' => 1000]],
        ];
    }
}

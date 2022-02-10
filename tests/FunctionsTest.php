<?php
/**
 * This file is part of Swow-Cloud/Job
 * @license  https://github.com/serendipity-swow/serendipity-job/blob/master/LICENSE
 */

declare(strict_types=1);

namespace Chevere\Xr\Tests\Chevere\Xr;

use Chevere\Writer\StreamWriter;
use Chevere\Xr\WriterInstance;
use PHPUnit\Framework\TestCase;
use function Chevere\Writer\streamTemp;
use function Chevere\Xr\getWriter;

/**
 * @internal
 * @coversNothing
 */
final class FunctionsTest extends TestCase
{
    public function testXr(): void
    {
        $previousWriter = getWriter();
        $writer = new StreamWriter(streamTemp(''));
        new WriterInstance($writer);
        $var = 'Hola xr!';
        $length = strlen($var);
        xr($var, t: 'Topic', e: '😎', f: XR_BACKTRACE | XR_PAUSE);
        $this->assertSame(
            '<pre>
Arg:0 <span style="color:#ff8700">string</span> ' . $var . ' <em><span style="color:rgb(108 108 108 / 65%);">(length=' . $length . ')</span></em></pre>',
            $writer->__toString()
        );
        new WriterInstance($previousWriter);
    }

    public function testXrr(): void
    {
        $this->expectNotToPerformAssertions();
        xrr('Hola xrr!');
    }
}

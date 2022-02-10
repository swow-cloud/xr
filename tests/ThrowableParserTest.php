<?php
/**
 * This file is part of Swow-Cloud/Job
 * @license  https://github.com/serendipity-swow/serendipity-job/blob/master/LICENSE
 */

declare(strict_types=1);

namespace Chevere\Xr\Tests\Chevere\Xr;

use Chevere\Throwable\Errors\TypeError;
use Chevere\Xr\ThrowableParser;
use Exception;
use PHPUnit\Framework\TestCase;
use function Chevere\Message\message;

/**
 * @internal
 * @coversNothing
 */
final class ThrowableParserTest extends TestCase
{
    public function testTopLevel(): void
    {
        $throwable = new Exception('foo');
        $parser = new ThrowableParser($throwable, '');
        $this->assertSame(Exception::class, $parser->topic());
        $this->assertSame(
            Exception::class,
            $parser->throwableRead()->className()
        );
        $this->assertSame('⚠️Throwable', $parser->emote());
        $this->assertStringContainsString(Exception::class, $parser->body());
    }

    public function testNamespaced(): void
    {
        $throwable = new TypeError(message: message('foo'));
        $parser = new ThrowableParser($throwable, '');
        $this->assertSame('TypeError', $parser->topic());
        $this->assertSame(
            TypeError::class,
            $parser->throwableRead()->className()
        );
        $this->assertStringContainsString(ThrowableParser::class, $parser->body());
    }

    public function testWithPrevious(): void
    {
        $throwable = new Exception('foo', previous: new Exception('bar'));
        $parser = new ThrowableParser($throwable, '');
        $this->assertStringContainsString(ThrowableParser::class, $parser->body());
    }

    public function testWithExtra(): void
    {
        $extra = 'EXTRA EXTRA! TODD SMELLS';
        $throwable = new Exception('foo');
        $parser = new ThrowableParser($throwable, $extra);
        $this->assertStringContainsString($extra, $parser->body());
    }
}

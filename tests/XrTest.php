<?php
/**
 * This file is part of Swow-Cloud/Job
 * @license  https://github.com/serendipity-swow/serendipity-job/blob/master/LICENSE
 */

declare(strict_types=1);

namespace Chevere\Xr\Tests\Chevere\Xr;

use Chevere\Xr\Client;
use Chevere\Xr\Xr;
use PHPUnit\Framework\TestCase;
use function Chevere\Filesystem\dirForPath;

/**
 * @internal
 * @coversNothing
 */
final class XrTest extends TestCase
{
    public function testConstructDefault(): void
    {
        $xr = new Xr();
        $args = [
            'enable' => true,
            'host' => 'localhost',
            'port' => 27420,
        ];
        foreach ($args as $prop => $value) {
            $this->assertSame($value, $xr->{$prop}());
        }
        $this->assertEquals(new Client(), $xr->client());
    }

    public function testConstructWithArguments(): void
    {
        $args = [
            'enable' => false,
            'host' => 'test',
            'port' => 1234,
        ];
        $xr = new Xr(...$args);
        foreach ($args as $prop => $value) {
            $this->assertSame($value, $xr->{$prop}());
        }
        $this->assertEquals(new Client($args['host'], $args['port']), $xr->client());
    }

    public function testConstructWithoutSettingsFileSubfolder(): void
    {
        $xr = (new Xr())
            ->withConfigDir(dirForPath(__DIR__ . '/_empty/_empty/'));
        $this->assertSame(true, $xr->enable());
        $this->assertEquals(new Client(), $xr->client());
    }

    public function testConstructWithDirNotExitst(): void
    {
        $xr = (new Xr())
            ->withConfigDir(dirForPath(__DIR__ . '/_not-found/'));
        $this->assertSame(true, $xr->enable());
        $this->assertEquals(new Client(), $xr->client());
    }

    public function testConstructWithSettingsFile(): void
    {
        $configDir = dirForPath(__DIR__ . '/_resources/');
        $return = include $configDir->path()->getChild('xr.php')->__toString();
        $xr = (new Xr())->withConfigDir($configDir);
        $this->assertSame($return['enable'], $xr->enable());
        unset($return['enable']);
        $this->assertEquals(new Client(...$return), $xr->client());
    }
}

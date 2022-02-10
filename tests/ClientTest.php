<?php
/**
 * This file is part of Swow-Cloud/Job
 * @license  https://github.com/serendipity-swow/serendipity-job/blob/master/LICENSE
 */

declare(strict_types=1);

namespace Chevere\Xr\Tests\Chevere\Xr;

use Chevere\Xr\Client;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 * @coversNothing
 */
final class ClientTest extends TestCase
{
    public function testDefault(): void
    {
        $client = new Client();
        $this->assertSame(
            'http://localhost:27420/endpoint',
            $client->getUrl('endpoint')
        );
    }

    public function testCustom(): void
    {
        $port = 12345;
        $host = 'test-host';
        $client = new Client(port: $port, host: $host);
        $this->assertSame(
            "http://{$host}:{$port}/endpoint",
            $client->getUrl('endpoint')
        );
    }
}

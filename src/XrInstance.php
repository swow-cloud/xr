<?php
/**
 * This file is part of Swow-Cloud/Job
 * @license  https://github.com/serendipity-swow/serendipity-job/blob/master/LICENSE
 */

declare(strict_types=1);

namespace Chevere\Xr;

use Chevere\Message\Message;
use Chevere\Throwable\Exceptions\LogicException;

/**
 * @codeCoverageIgnore
 */
final class XrInstance
{
    private static Xr $instance;

    public function __construct(Xr $xr)
    {
        self::$instance = $xr;
    }

    public static function get(): Xr
    {
        if (!isset(self::$instance)) {
            throw new LogicException(
                new Message('No xr instance present')
            );
        }

        return self::$instance;
    }
}

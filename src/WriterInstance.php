<?php
/**
 * This file is part of Swow-Cloud/Job
 * @license  https://github.com/serendipity-swow/serendipity-job/blob/master/LICENSE
 */

declare(strict_types=1);

namespace Chevere\Xr;

use Chevere\Message\Message;
use Chevere\Throwable\Exceptions\LogicException;
use Chevere\Writer\Interfaces\WriterInterface;

/**
 * @codeCoverageIgnore
 */
final class WriterInstance
{
    private static WriterInterface $instance;

    public function __construct(WriterInterface $writer)
    {
        self::$instance = $writer;
    }

    public static function get(): WriterInterface
    {
        if (!isset(self::$instance)) {
            throw new LogicException(
                new Message('No writer instance present')
            );
        }

        return self::$instance;
    }
}

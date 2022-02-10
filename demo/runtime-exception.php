<?php
/**
 * This file is part of Swow-Cloud/Job
 * @license  https://github.com/serendipity-swow/serendipity-job/blob/master/LICENSE
 */

declare(strict_types=1);

use Chevere\Throwable\Exceptions\RuntimeException;
use function Chevere\Message\message;

throw new RuntimeException(
    message: message('Ch bah puta la güeá'),
    code: 12345,
    previous: new Exception(
        message: 'A la chuchesumare',
        code: 678,
        previous: new LogicException(
            message: 'Ese conchesumare',
            code: 0,
        )
    )
);

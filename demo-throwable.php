<?php
/**
 * This file is part of Swow-Cloud/Job
 * @license  https://github.com/serendipity-swow/serendipity-job/blob/master/LICENSE
 */

declare(strict_types=1);

use Chevere\ThrowableHandler\ThrowableHandler;
use Chevere\Writer\StreamWriter;
use Chevere\Writer\Writers;
use Chevere\Writer\WritersInstance;
use function Chevere\Writer\streamFor;
use function Chevere\Xr\registerThrowableHandler;

foreach (['/', '/../../../'] as $path) {
    $autoload = __DIR__ . $path . 'vendor/autoload.php';
    if (stream_resolve_include_path($autoload)) {
        require $autoload;

        break;
    }
}

new WritersInstance(
    (new Writers())
        ->withOutput(
            new StreamWriter(
                streamFor('php://stdout', 'w')
            )
        )
        ->withError(
            new StreamWriter(
                streamFor('php://stderr', 'w')
            )
        )
);
set_error_handler(
    ThrowableHandler::ERRORS_AS_EXCEPTIONS
);
register_shutdown_function(
    ThrowableHandler::FATAL_ERROR_HANDLER
);
set_exception_handler(
    ThrowableHandler::CONSOLE_HANDLER
);
registerThrowableHandler(true);

include __DIR__ . '/demo/runtime-exception.php';

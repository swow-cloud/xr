<?php
/**
 * This file is part of Swow-Cloud/Job
 * @license  https://github.com/serendipity-swow/serendipity-job/blob/master/LICENSE
 */

declare(strict_types=1);

namespace Chevere\Xr {
    use Chevere\Writer\Interfaces\WriterInterface;
    use Chevere\Writer\StreamWriter;
    use LogicException;
    use Throwable;
    use function Chevere\Filesystem\dirForPath;
    use function Chevere\Writer\streamTemp;

    /**
     * @codeCoverageIgnore
     */
    function getWriter(): WriterInterface
    {
        try {
            return WriterInstance::get();
        } catch (LogicException) {
            $writer = new StreamWriter(streamTemp(''));

            return (new WriterInstance($writer))::get();
        }
    }

    function getXr(): Xr
    {
        try {
            return XrInstance::get();
        } catch (LogicException) {
            $xr = (new Xr())
                ->withConfigDir(
                    dirForPath(getcwd())
                );

            return (new XrInstance($xr))::get();
        }
    }

    /**
     * Register XR throwable handler.
     *
     * @param bool $callPrevious True to call the previous handler.
     *                           False to disable the previous handler.
     *
     * @codeCoverageIgnore
     */
    function registerThrowableHandler(bool $callPrevious = true): void
    {
        /** @var callable $xrHandler */
        $xrHandler = __NAMESPACE__ . '\\throwableHandler';
        $previous = set_exception_handler($xrHandler);
        if ($callPrevious === false || $previous === null) {
            return;
        }
        set_exception_handler(
            function (Throwable $throwable) use ($xrHandler, $previous) {
                $xrHandler($throwable);
                $previous($throwable);
            }
        );
    }

    /**
     * Handle a Throwable using XR.
     *
     * @param Throwable $throwable The throwable to handle
     * @param string $extra Extra contents to append to the XR message
     *
     * @codeCoverageIgnore
     */
    function throwableHandler(Throwable $throwable, string $extra = ''): void
    {
        if (getXr()->enable() === false) {
            return; // @codeCoverageIgnore
        }
        $parser = new ThrowableParser($throwable, $extra);
        getXr()->client()
            ->sendMessage(
                (new Message(
                    backtrace: $parser->throwableRead()->trace(),
                ))
                    ->withBody($parser->body())
                    ->withTopic($parser->topic())
                    ->withEmote($parser->emote())
            );
    }
}

namespace {
    use Chevere\Xr\Message;
    use function Chevere\Xr\getWriter;
    use function Chevere\Xr\getXr;

// @codeCoverageIgnoreStart
    if (!defined('XR_BACKTRACE')) {
        define('XR_BACKTRACE', 1);
    }
    if (!defined('XR_PAUSE')) {
        define('XR_PAUSE', 2);
    }
    // @codeCoverageIgnoreEnd
    if (!function_exists('xr')) { // @codeCoverageIgnore
        /**
         * Dumps information about one or more variables to XR.
         *
         * ```php
         * xr($foo, $bar,...);
         * ```
         *
         * @param mixed ...$vars Variable(s) to dump
         * @param string $t Topic
         * @param string $e Emote
         * @param int $f `XR_BACKTRACE | XR_PAUSE`
         */
        function xr(...$vars): void
        {
            if (getXr()->enable() === false) {
                return; // @codeCoverageIgnore
            }
            $defaultArgs = ['e' => '', 't' => '', 'f' => 0];
            $args = array_merge($defaultArgs, $vars);
            foreach (array_keys($defaultArgs) as $name) {
                if (array_key_exists($name, $vars)) {
                    unset($vars[$name]);
                }
            }
            getXr()->client()
                ->sendMessage(
                    (new Message(
                        backtrace: debug_backtrace(),
                    ))
                        ->withWriter(getWriter())
                        ->withVars(...$vars)
                        ->withTopic(strval($args['t']))
                        ->withEmote(strval($args['e']))
                        ->withFlags(intval($args['f']))
                );
        }
    }

    if (!function_exists('xrr')) { // @codeCoverageIgnore
        /**
         * Send a raw html message to XR.
         *
         * ```php
         * xrr($html, ...);
         * ```
         *
         * @param string $html Message to send
         * @param string $t Topic
         * @param string $e Emote
         * @param int $f `XR_BACKTRACE | XR_PAUSE`
         *
         * @codeCoverageIgnore
         */
        function xrr(
            string $html,
            string $t = '',
            string $e = '',
            int $f = 0
        ): void {
            if (getXr()->enable() === false) {
                return;
            }
            getXr()->client()
                ->sendMessage(
                    (new Message(
                        backtrace: debug_backtrace(),
                    ))
                        ->withBody($html)
                        ->withTopic($t)
                        ->withEmote($e)
                        ->withFlags($f)
                );
        }
    }
}

<?php

/*
 * This file is part of Chevere.
 *
 * (c) Rodolfo Berrios <rodolfo@chevere.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Chevere\Xr\Components\Xr;

use Chevere\Components\ThrowableHandler\Formatters\ThrowableHandlerHtmlFormatter;
use Chevere\Components\ThrowableHandler\ThrowableTraceFormatter;
use Chevere\Components\VarDump\Formatters\VarDumpHtmlFormatter;
use Chevere\Components\VarDump\VarDump;
use Chevere\Interfaces\Writer\WriterInterface;
use Chevere\Xr\Components\VarDump\Outputters\VarDumpHtmlOutputter;

final class Message
{
    private array $backtrace = [];
    
    private array $data = [];

    public function __construct(
        private WriterInterface $writer,
        private array $vars,
        private int $shift = 1
    ) {
        $this->backtrace = debug_backtrace();
        for ($i = 1; $i <= $this->shift; $i++) {
            array_shift($this->backtrace);
        }
        (new VarDump(
            new VarDumpHtmlFormatter(),
            new VarDumpHtmlOutputter()
        ))
            ->withShift($this->shift)
            ->withVars(...$this->vars)
            ->process($this->writer);
        $dumpString = $this->writer->toString();
        $body = $dumpString !== ''
            ? '<div class="dump">' . $dumpString . '</div>'
            : '';
        $this->data = [
            'body' => $body,
            'file_path' => (string) ($this->backtrace[0]['file'] ?? ''),
            'file_line' => (string) ($this->backtrace[0]['line'] ?? ''),
            'emote' => '',
            'topic' => '',
            'pause' => '0',
        ];
    }

    public function withTopic(string $topic): self
    {
        $new = clone $this;
        $new->data['topic'] = $topic;

        return $new;
    }

    public function withEmote(string $emote): self
    {
        $new = clone $this;
        $new->data['emote'] = $emote;

        return $new;
    }

    public function withFlags(int $flags): self
    {
        $new = clone $this;
        if ($flags & XR_BACKTRACE) {
            $traceFormatter = new ThrowableTraceFormatter(
                $new->backtrace,
                new ThrowableHandlerHtmlFormatter()
            );
            $new->data['body'] .= '<div class="backtrace">' . $traceFormatter->toString() . '</div>';
        }
        if ($flags & XR_PAUSE) {
            $new->data['pause'] = '1';
        }

        return $new;
    }

    public function backtrace(): array
    {
        return $this->backtrace;
    }

    public function data(): array
    {
        return $this->data;
    }
}

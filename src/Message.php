<?php
/**
 * This file is part of Swow-Cloud/Job
 * @license  https://github.com/serendipity-swow/serendipity-job/blob/master/LICENSE
 */

declare(strict_types=1);

namespace Chevere\Xr;

use Chevere\Common\Interfaces\ToArrayInterface;
use Chevere\ThrowableHandler\Formats\ThrowableHandlerHtmlFormat;
use Chevere\Trace\TraceDocument;
use Chevere\VarDump\Formats\VarDumpHtmlFormat;
use Chevere\VarDump\VarDump;
use Chevere\Writer\Interfaces\WriterInterface;
use Chevere\Writer\NullWriter;
use Chevere\Xr\VarDump\Output\VarDumpHtmlOutput;

final class Message implements ToArrayInterface
{
    private string $body = '';

    private string $topic = '';

    private string $emote = '';

    private string $filePath = '';

    private int $fileLine = 0;

    private bool $isPause = false;

    private bool $isBacktrace = false;

    private array $vars = [];

    private WriterInterface $writer;

    public function __construct(private array $backtrace = [])
    {
        if ($backtrace === []) {
            $this->backtrace = debug_backtrace();
        }
        $this->writer = new NullWriter();
        $this->filePath = strval($this->backtrace[0]['file'] ?? '');
        $this->fileLine = intval($this->backtrace[0]['line'] ?? 0);
    }

    public function body(): string
    {
        return $this->body;
    }

    public function topic(): string
    {
        return $this->topic;
    }

    public function emote(): string
    {
        return $this->emote;
    }

    public function filePath(): string
    {
        return $this->filePath;
    }

    public function fileLine(): int
    {
        return $this->fileLine;
    }

    public function isBacktrace(): bool
    {
        return $this->isBacktrace;
    }

    public function isPause(): bool
    {
        return $this->isPause;
    }

    public function vars(): array
    {
        return $this->vars;
    }

    public function writer(): WriterInterface
    {
        return $this->writer;
    }

    public function withBody(string $body): self
    {
        $new = clone $this;
        $new->body = $body;

        return $new;
    }

    public function withTopic(string $topic): self
    {
        $new = clone $this;
        $new->topic = $topic;

        return $new;
    }

    public function withEmote(string $emote): self
    {
        $new = clone $this;
        $new->emote = $emote;

        return $new;
    }

    public function withWriter(WriterInterface $writer): self
    {
        $new = clone $this;
        $new->writer = $writer;

        return $new;
    }

    public function withVars(...$vars): self
    {
        $new = clone $this;
        $new->vars = $vars;

        return $new;
    }

    public function withFlags(int $flags): self
    {
        $new = clone $this;
        if ($flags & XR_BACKTRACE) {
            $new->isBacktrace = true;
        }
        if ($flags & XR_PAUSE) {
            $new->isPause = true;
        }

        return $new;
    }

    public function toArray(): array
    {
        $this->handleDumpVars();
        $this->handleBacktrace();

        return [
            'body' => $this->body,
            'file_path' => $this->filePath,
            'file_line' => strval($this->fileLine),
            'emote' => $this->emote,
            'topic' => $this->topic,
            'pause' => strval(intval($this->isPause)),
        ];
    }

    private function handleDumpVars(): void
    {
        if ($this->vars === []) {
            return;
        }
        (new VarDump(
            new VarDumpHtmlFormat(),
            new VarDumpHtmlOutput()
        ))
            ->withVars(...$this->vars)
            ->process($this->writer);
        $dumpString = $this->writer->__toString();
        if ($dumpString !== '') {
            $this->body .= '<div class="dump">' . $dumpString . '</div>';
        }
    }

    private function handleBacktrace(): void
    {
        if ($this->isBacktrace) {
            $traceDocument = new TraceDocument(
                $this->backtrace,
                new ThrowableHandlerHtmlFormat()
            );
            $this->body .= '<div class="backtrace">'
                . "\n"
                . $traceDocument->__toString()
                . '</div>';
        }
    }
}

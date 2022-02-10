<?php
/**
 * This file is part of Swow-Cloud/Job
 * @license  https://github.com/serendipity-swow/serendipity-job/blob/master/LICENSE
 */

declare(strict_types=1);

namespace Chevere\Xr\VarDump\Output;

use Chevere\VarDump\Interfaces\VarDumpFormatInterface;
use Chevere\VarDump\Outputs\VarDumpAbstractOutput;

final class VarDumpHtmlOutput extends VarDumpAbstractOutput
{
    public function tearDown(): void
    {
        $this->writer()->write('</pre>');
    }

    public function prepare(): void
    {
        $this->caller = '';
        $this->writer()->write('<pre>');
    }

    public function writeCallerFile(VarDumpFormatInterface $formatter): void
    {
        // null override
    }
}

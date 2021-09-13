<?php
/**
 * This code is licensed under the BSD 3-Clause License.
 *
 * Copyright (c) 2017, Maks Rafalko
 * All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions are met:
 *
 * * Redistributions of source code must retain the above copyright notice, this
 *   list of conditions and the following disclaimer.
 *
 * * Redistributions in binary form must reproduce the above copyright notice,
 *   this list of conditions and the following disclaimer in the documentation
 *   and/or other materials provided with the distribution.
 *
 * * Neither the name of the copyright holder nor the names of its
 *   contributors may be used to endorse or promote products derived from
 *   this software without specific prior written permission.
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS"
 * AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE
 * IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE
 * DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT HOLDER OR CONTRIBUTORS BE LIABLE
 * FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL
 * DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR
 * SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER
 * CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY,
 * OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE
 * OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 */

declare(strict_types=1);

namespace Infection\Process\Factory;

use Composer\InstalledVersions;
use Infection\AbstractTestFramework\TestFrameworkAdapter;
use Infection\Process\OriginalPhpProcess;
use function method_exists;
use Symfony\Component\Process\Process;
use function version_compare;

/**
 * @internal
 * @final
 */
class InitialTestsRunProcessFactory
{
    private TestFrameworkAdapter $testFrameworkAdapter;

    public function __construct(TestFrameworkAdapter $testFrameworkAdapter)
    {
        $this->testFrameworkAdapter = $testFrameworkAdapter;
    }

    /**
     * Creates process with enabled debugger as test framework is going to use in the code coverage.
     *
     * @param string[] $phpExtraOptions
     */
    public function createProcess(
        string $testFrameworkExtraOptions,
        array $phpExtraOptions,
        bool $skipCoverage
    ): Process {
        // If we're expecting to receive a code coverage, test process must run in a vanilla environment
        $processClass = $skipCoverage ? Process::class : OriginalPhpProcess::class;

        /** @var Process $process */
        $process = new $processClass(
            $this->testFrameworkAdapter->getInitialTestRunCommandLine(
                $testFrameworkExtraOptions,
                $phpExtraOptions,
                $skipCoverage
            )
        );

        $process->setTimeout(null); // Ignore the default timeout of 60 seconds

        if (method_exists($process, 'inheritEnvironmentVariables') && version_compare((string) InstalledVersions::getReference('symfony/console'), 'v4.4', '<')) {
            // In version 4.4.0 this method is deprecated and removed in 5.0.0
            $process->inheritEnvironmentVariables();
        }

        return $process;
    }
}

<?php

require_once __DIR__ . '/HTML/TestResult.php';
require_once __DIR__ . '/HTML/ClassResult.php';

/**
 * PHPUnit
 *
 * Copyright (c) 2001-2013, Sebastian Bergmann <sebastian@phpunit.de>.
 * All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions
 * are met:
 *
 *   * Redistributions of source code must retain the above copyright
 *     notice, this list of conditions and the following disclaimer.
 *
 *   * Redistributions in binary form must reproduce the above copyright
 *     notice, this list of conditions and the following disclaimer in
 *     the documentation and/or other materials provided with the
 *     distribution.
 *
 *   * Neither the name of Sebastian Bergmann nor the names of his
 *     contributors may be used to endorse or promote products derived
 *     from this software without specific prior written permission.
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS
 * "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT
 * LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS
 * FOR A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE
 * COPYRIGHT OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT,
 * INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING,
 * BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES;
 * LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER
 * CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT
 * LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN
 * ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE
 * POSSIBILITY OF SUCH DAMAGE.
 *
 * @package    PHPUnit
 * @subpackage Util_TestDox
 * @author     Sebastian Bergmann <sebastian@phpunit.de>
 * @author     Thibaud Fabre <thibaud@evaneos.com>
 * @copyright  2001-2013 Sebastian Bergmann <sebastian@phpunit.de>
 * @license    http://www.opensource.org/licenses/BSD-3-Clause  The BSD 3-Clause License
 * @link       http://www.phpunit.de/
 * @since      File available since Release 2.3.0
 */

/**
 * Prints TestDox documentation in HTML format.
 *
 * @package PHPUnit
 * @subpackage Util_TestDox
 * @author Sebastian Bergmann <sebastian@phpunit.de>
 * @copyright 2001-2013 Sebastian Bergmann <sebastian@phpunit.de>
 * @license http://www.opensource.org/licenses/BSD-3-Clause The BSD 3-Clause License
 * @link http://www.phpunit.de/
 * @since Class available since Release 2.1.0
 */
class PHPUnit_Util_TestDox_ResultPrinter_HTML extends PHPUnit_Util_TestDox_ResultPrinter
{

    /**
     *
     * @var PHPUnit_Util_TestDox_ResultPrinter_HTML_ClassResult
     */
    private $currentClass = null;

    /**
     *
     * @var PHPUnit_Util_TestDox_ResultPrinter_HTML_TestResult
     */
    private $currentTest = null;

    /**
     *
     * @var PHPUnit_Util_TestDox_ResultPrinter_HTML_ClassResult[]
     */
    private $classes = array();

    /**
     *
     * @var boolean
     */
    protected $printsHTML = TRUE;

    /**
     * Handler for 'start run' event.
     */
    protected function startRun()
    {}

    /**
     * Handler for 'start class' event.
     *
     * @param string $name
     */
    protected function startClass($name)
    {
        $classResult = new PHPUnit_Util_TestDox_ResultPrinter_HTML_ClassResult($name);

        $this->currentClass = $classResult;
        $this->classes[$name] = $classResult;
    }

    public function startTest(PHPUnit_Framework_Test $test)
    {
        parent::startTest($test);

        $result = new PHPUnit_Util_TestDox_ResultPrinter_HTML_TestResult($this->currentTestClassPrettified,
            $this->currentTestMethodPrettified);

        if ($test instanceof PHPUnit_Framework_TestCase &&
            ! $test instanceof PHPUnit_Framework_Warning) {
            $annotations = $test->getAnnotations();

            if (isset($annotations['method']['dataProvider'])) {
                $result->enableDatasets();
            }
        }

        $this->currentClass->addTest($result);
        $this->currentTest = $result;
    }

    public function endTest(PHPUnit_Framework_Test $test, $time)
    {
        $this->currentTest->setDuration($time);
        $this->currentTest = null;

        parent::endTest($test, $time);
    }

    public function addError(PHPUnit_Framework_Test $test, Exception $e, $time)
    {
        $this->currentTest->addError($e);
        parent::addError($test, $e, $time);
    }

    public function addFailure(PHPUnit_Framework_Test $test, PHPUnit_Framework_AssertionFailedError $e, $time)
    {
        $this->currentTest->addFailure($e);
        parent::addFailure($test, $e, $time);
    }

    /**
     * Handler for 'end run' event.
     *
     * @todo Clean up, remove inline html
     */
    protected function endRun()
    {
        $head = <<<EOR
            <!DOCTYPE html><html lang="en">
                <head>
                    <link href="bootstrap/3.0.2/css/bootstrap.min.css" rel="stylesheet">
                    <style type="text/css">
                        @keyframes toggleHoverSuccess { from { background-color: #dff0d8 } to { background-color: inherit } }
                        @-webkit-keyframes toggleHoverSuccess { from { background-color: #dff0d8 } to { background-color: inherit } }
                        @keyframes toggleHoverError { from { background-color: #f2dede } to { background-color: inherit } }
                        @-webkit-keyframes toggleHoverError { from { background-color: #f2dede } to { background-color: inherit } }
                        .list-group-item .list-item { margin-top: 1em; }
                        .list-item .well.well-sm { margin-top: 1em; }
                        .list-hover > .sublist { display: none; }
                        .list-hover:hover > .sublist { display: block; }
                        .list-hover.alert-success:hover { -webkit-animation: toggleHoverSuccess 2s; animation: toggleHoverSuccess 2s; }
                        .list-hover.alert-warning:hover { -webkit-animation: toggleHoverError 2s; animation: toggleHoverError 2s; }
                        .glyphicon.alert-danger, .glyphicon.alert-info, .glyphicon.alert-success { background-color: inherit !important }
                    </style>
                </head>
                <body><div class="container">
                <div class="page-header"><h1>Test results<span></h1>
EOR;
        $this->write($head);

        if ($this->failureCount) {
            $plural = ($this->failureCount == 1) ? '' : 's';
            $this->write('<div class="alert alert-danger">' . $this->failureCount . ' test' . $plural . ' failed</div>');
        }

        if ($this->successCount) {
            $plural = ($this->successCount == 1) ? '' : 's';
            $this->write(
                '<div class="alert alert-success">' . $this->successCount . ' test' . $plural . ' passed</div>');
        }

        if (! $this->failureCount && ! $this->successCount) {
            $this->write('<div class="alert alert-info">No tests were run</div>');
        }

        $this->write('</div>');
        $this->write('<h2>Table of Contents</h2><div class="list-group">');

        foreach ($this->classes as $name => $classResult) {
            $class = '';
            $linkClass = '';
            $badge = '';

            if (! $classResult->hasSuccesses() && $classResult->hasErrors()) {
                $danger = true;
                $class = 'alert alert-danger';
                $linkClass = 'alert-link';
                $badge = '<span class="pull-right label label-danger">Danger</span>';
            }
            elseif ($classResult->hasErrors()) {
                $class = 'alert alert-warning';
                $warning = true;
                $linkClass = 'alert-link';
                $badge = '<span class="pull-right label label-warning">Warning</span>';
            }
            else {
                $class = 'alert alert-success';
                $badge = '<span class="pull-right label label-success">Success</span>';
            }

            $this->write('<div class="list-hover list-group-item ' . $class . '">');
            $this->write($badge);
            $this->write('&nbsp;<a href="#' . $name . '" class="' . $linkClass . '">' . $classResult->getClassName() . '</a>');

            $this->write('<div class="sublist">');
            foreach ($classResult->getTests() as $test) {
                /* @var $test PHPUnit_Util_Testdox_ResultPrinter_HTML_TestResult */
                $this->write(
                    '<div class="list-item"><span class="glyphicon glyphicon-unchecked"></span>&nbsp;' . $test->getTestName() .
                         '</div>');
            }

            $this->write('</div></div>');
        }

        $this->write('</div>');

        foreach ($this->classes as $name => $classResult) {
            $this->write(
                '<a id="' . $name . '"><h2 id="' . $name . '">' . $classResult->getClassName() . '</h2></a><ul class="list-group">');

            foreach($classResult->getTests() as $result) {
                if ($result->hasErrors()) {
                    $class = 'glyphicon-unchecked alert-danger';
                }
                elseif ($result->wasSkipped()) {
                    $class = 'glyphicon-unchecked alert-warning';
                }
                else {
                    $class = 'glyphicon-check alert-success';
                }

                $this->write(
                    '<li class="list-group-item list-hover"><span class="glyphicon ' . $class . '"></span>&nbsp;' .
                    $result->getTestName());

                $this->writeDuration($result->getDuration());
                $this->writeError($result);
                $this->write('</li>');
            }
            $this->write('</ul>');
        }

        $this->write('</div></body></html>');
        $this->copyBootstrapFiles();
    }

    private function writeDuration($duration)
    {
        $duration = number_format($duration * bcpow(10, 6), 4);
        $this->write(' <span class="badge badge-light">' . $duration . ' &#181;s</span>');
    }

    private function writeError($result)
    {
        if (! $result->hasErrors())
            return;

        $this->write('<div class="sublist">');

        foreach ($result->getErrors() as $error) {
            if ($error instanceof PHPUnit_Framework_AssertionFailedError) {
                /* var $error PHPUnit_Framework_AssertionFailedError */
                $this->write(
                    '<div class="list-item"><strong>Assertion failed</strong> : ' . nl2br($error->getMessage()) .
                         '</div>');
            }
            else {
                /* @var $error Exception */
                $trace = $this->formatTrace(PHPUnit_Util_Filter::getFilteredStacktrace($error, false));
                $this->write(
                    '<div class="list-item"><strong>Error</strong> : ' . $error->getMessage() .
                         '<div class="well well-sm"><strong>Stack trace</strong>' . nl2br($trace) . '</div></div>');
            }
        }

        $this->write('</div>');
    }

    private function formatTrace(array $trace)
    {
        $traceBuffer = '';
        $traceFormat  = PHP_EOL . '<strong>#%d</strong> %s(%d) : %s%s%s(%s)';
        $i = 0;

        foreach ($trace as $frame) {
            $traceBuffer .= sprintf($traceFormat, $i ++, $frame['file'], $frame['line'], $frame['class'], $frame['type'],
                $frame['function'], implode(', ', $this->getPrintableArgs($frame['args'])));
        }

        return $traceBuffer;
    }

    private function getPrintableArgs(array $args)
    {
        $printable = array();

        foreach ($args as $arg) {
            if (is_array($arg)) {
                $argString = 'Array';
            }
            elseif (is_object($arg)) {
                if (method_exists($arg, '__toString')) {
                    $argString = "'" . $this->truncateIfNecessary((string) $arg) . "'";
                }
                else {
                    $argString = sprintf('Object(%s)', get_class($arg));
                }
            }
            else {
                $argString = $this->truncateIfNecessary((string) $arg);
                if (is_string($arg)) {
                    $argString = "'" . $argString . "'";
                }
            }

            $printable[] = $argString;
        }

        return $printable;
    }

    private function truncateIfNecessary($string)
    {
        if (strlen($string) > 15) {
            $string = substr($string, 0, 15) . '...';
        }

        return $string;
    }

    private function copyBootstrapFiles($directory = null, $targetDirectory = null)
    {
        $directory = $directory ?  : dirname(__FILE__) . DIRECTORY_SEPARATOR . 'bootstrap';

        $inDir = dir($directory);
        $outDir = $targetDirectory ? $targetDirectory : dirname($this->outTarget) . DIRECTORY_SEPARATOR . 'bootstrap';

        if (is_dir($outDir) != true) {
            mkdir($outDir, 0777, true);
        }

        while ($file = $inDir->read()) {
            if ($file == '..' || $file == '.') {
                continue;
            }
            else {
                $sourceFile = $directory . DIRECTORY_SEPARATOR . $file;
                $destFile = $outDir . DIRECTORY_SEPARATOR . $file;

                if (is_dir($sourceFile)) {
                    $this->copyBootstrapFiles($sourceFile, $destFile);
                }
                else {
                    copy($sourceFile, $destFile);
                }
            }
        }
    }
}

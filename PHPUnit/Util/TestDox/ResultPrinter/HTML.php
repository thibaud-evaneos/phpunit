<?php

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
    private $currentClass;

    private $classes = array();

    private $failures = array();

    private $successes = array();

    private $failureCount = 0;

    private $successCount = 0;

    /**
     *
     * @var boolean
     */
    protected $printsHTML = TRUE;

    /**
     * Handler for 'start run' event.
     */
    protected function startRun()
    {

    }

    /**
     * Handler for 'start class' event.
     *
     * @param string $name
     */
    protected function startClass($name)
    {
        $this->currentClass = $name;

        $this->classes[$name] = $this->currentTestClassPrettified;
        $this->failures[$name] = array();
        $this->successes[$name] = array();
    }

    /**
     * Handler for 'on test' event.
     *
     * @param string $name
     * @param boolean $success
     */
    protected function onTest($name, $success = TRUE)
    {
        if (! $success) {
            $this->failureCount++;
            $this->failures[$this->currentClass][] = $name;
        }
        else {
            $this->successCount++;
            $this->successes[$this->currentClass][] = $name;
        }
    }

    /**
     * Handler for 'end class' event.
     *
     * @param string $name
     */
    protected function endClass($name)
    {}

    /**
     * Handler for 'end run' event.
     */
    protected function endRun()
    {
        $this->write('<!DOCTYPE html><html lang="en">');

        $this->write('<head>');
        $this->write('<link href="http://netdna.bootstrapcdn.com/bootstrap/3.0.2/css/bootstrap.min.css" rel="stylesheet">');
        $this->write('</head>');

        $this->write('<body><div class="container">');
        $this->write('<div class="page-header"><h1>Test results</h1>');

        if ($this->failureCount) {
            $plural = ($this->failureCount == 1) ? '' : 's';
            $this->write('<div class="alert alert-danger">' . $this->failureCount . ' test' . $plural . ' failed</div>');
        }

        if ($this->successCount) {
            $plural = ($this->successCount == 1) ? '' : 's';
            $this->write('<div class="alert alert-success">' . $this->successCount . ' test' . $plural . ' passed</div>');
        }

        if (!$this->failureCount && !$this->successCount) {
            $this->write('<div class="alert alert-info">No tests were run</div>');
        }

        $this->write('</div>');

        $this->write('<h2>Table of Contents</h2><ul class="list-group">');

        foreach ($this->classes as $name => $prettyName) {
            $class = '';
            $linkClass = '';
            $danger = false;
            $warning = false;

            if (!count($this->successes[$name]) && count($this->failures[$name])) {
                $danger = true;
                $class = ' alert alert-danger';
                $linkClass = 'alert-link';
            }
            elseif (count($this->failures[$name])) {
                $class = ' alert alert-warning';
                $warning = true;
                $linkClass = 'alert-link';
            }

            $this->write('<li class="list-group-item' . $class . '">');
            if ($danger) {
                $this->write('<span class="pull-right label label-danger">Danger</span>');
            } elseif ($warning) {
                $this->write('<span class="pull-right label label-warning">Warning</span>');
            } else {
                $this->write('<span class="pull-right label label-success">Success</span>');
            }
            $this->write('&nbsp;<a href="#' .$name . '" class="' . $linkClass . '">' . $prettyName . '</a>');
            $this->write('</li>');
        }

        $this->write('</ul>');

        foreach ($this->classes as $name => $prettyName) {
            $this->write('<a id="' . $name . '"><h2 id="' . $name . '">' . $prettyName . '</h2></a><ul class="list-group">');

            if (count($this->failures[$name])) {
                foreach ($this->failures[$name] as $test) {
                    $this->write('<li class="list-group-item alert alert-danger"><span class="glyphicon glyphicon-unchecked"></span>&nbsp;' . $test . '</li>');
                }
            }

            if (count($this->successes[$name])) {
                foreach ($this->successes[$name] as $test) {
                    $this->write('<li class="list-group-item"><span class="glyphicon glyphicon-check"></span>&nbsp;' . $test . '</li>');
                }
            }

            $this->write('</ul>');
        }

        $this->write('</div></body></html>');
    }
}

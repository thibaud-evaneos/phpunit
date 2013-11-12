<?php

class PHPUnit_Util_TestDox_ResultPrinter_HTML_ClassResult {

    private $className;

    private $tests = array();

    public function __construct($className)
    {
        $this->className = $className;
    }

    public function addTest(PHPUnit_Util_TestDox_ResultPrinter_HTML_TestResult $result)
    {
        if (!isset($this->tests[$result->getTestName()])) {
            $this->tests[$result->getTestName()] = $result;
        }
        else {

        }
    }

    public function getTests()
    {
        return $this->tests;
    }

    public function getClassName()
    {
        return $this->className;
    }

    public function hasErrors()
    {
        foreach ($this->tests as $test) {
            if ($test->hasErrors())
                return true;
        }

        return false;
    }

    public function getErrorCount()
    {
        $i = 0;

        foreach ($this->tests as $test) {
            if ($test->hasErrors())
                $i++;
        }

        return $i;
    }

    public function hasSuccesses()
    {
        foreach ($this->tests as $test) {
            if (! $test->hasErrors())
                return true;
        }

        return false;
    }
}
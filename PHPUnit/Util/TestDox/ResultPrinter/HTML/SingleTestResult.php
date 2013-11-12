<?php

class PHPUnit_Util_TestDox_ResultPrinter_HTML_SingleTestResult {

    private $testClass;

    private $testName;

    private $duration = 0;

    private $isSkipped = false;

    private $hasDatasets = false;

    private $datasets = null;

    private $errors = array();

    private $failures = array();

    public function __construct($testClass, $testName)
    {
        $this->testClass = $testClass;
        $this->testName = $testName;
    }

    public function addError(Exception $e)
    {
        $this->errors[] = $e;
    }

    public function addFailure(PHPUnit_Framework_AssertionFailedError $e) {
        $this->failures[] = $e;
    }

    public function hasErrors()
    {
        return count($this->errors) > 0 || count($this->failures) > 0;
    }

    public function getErrors()
    {
        return array_merge($this->errors, $this->failures);
    }

    public function wasSkipped()
    {
        return $this->isSkipped;
    }

    public function setDuration($elapsed)
    {
        $this->duration = $elapsed;
    }

    public function getDuration()
    {
        return $this->duration;
    }

    public function getTestClass()
    {
        return $this->testClass;
    }

    public function getTestName()
    {
        return $this->testName;
    }

    public function enableDatasets()
    {
        $this->hasDatasets = true;
        $this->datasets = $this->datasets ?: array();
    }

    public function hasDatasets()
    {
        return $this->hasDatasets;
    }

    public function getDatasets()
    {
        return $this->datasets;
    }

    public function addDataset()
    {

    }
}
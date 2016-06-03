<?php

class SimpleTestListener implements PHPUnit_Framework_TestListener
{
    private $_time;
    private $_timeLimit = 5;

    public function startTest(PHPUnit_Framework_Test $test)
    {
        $this->_time = time();
    }

    public function endTest(PHPUnit_Framework_Test $test, $time)
    {
        $current = time();
        $took = $current - $this->_time;
        if ($took > $this->_timeLimit) {
            $class = get_class($test);
            echo "\nSLOW TEST: {$took} second" . ($took == 1 ? '' : 's') . " for {$class}::{$test->getName()}\n";
        }
    }

    public function addError(PHPUnit_Framework_Test $test, Exception $e, $time)
    {
//         printf("Error while running test '%s'.\n", $test->getName());
    }

    public function addFailure(PHPUnit_Framework_Test $test, PHPUnit_Framework_AssertionFailedError $e, $time)
    {
//         printf("Test '%s' failed.\n", $test->getName());
    }

    public function addIncompleteTest(PHPUnit_Framework_Test $test, Exception $e, $time)
    {
//         printf("Test '%s' is incomplete.\n", $test->getName());
    }

    public function addSkippedTest(PHPUnit_Framework_Test $test, Exception $e, $time)
    {
//         printf("Test '%s' has been skipped.\n", $test->getName());
    }

    public function startTestSuite(PHPUnit_Framework_TestSuite $suite)
    {
//         printf("TestSuite '%s' started.\n", $suite->getName());
    }

    public function endTestSuite(PHPUnit_Framework_TestSuite $suite)
    {
//         printf("TestSuite '%s' ended.\n", $suite->getName());
    }

    public function addRiskyTest(PHPUnit_Framework_Test $test, Exception $e, $time)
    {
//         printf("Test '%s' is risky.\n", $test->getName());
    }
}

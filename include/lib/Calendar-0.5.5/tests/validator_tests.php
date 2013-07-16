<?php
// $Id: validator_tests.php 4940 2012-06-06 02:20:28Z fishbone $

require_once('simple_include.php');
require_once('calendar_include.php');

class ValidatorTests extends GroupTest {
    function ValidatorTests() {
        $this->GroupTest('Validator Tests');
        $this->addTestFile('validator_unit_test.php');
        $this->addTestFile('validator_error_test.php');
    }
}

if (!defined('TEST_RUNNING')) {
    define('TEST_RUNNING', true);
    $test = &new ValidatorTests();
    $test->run(new HtmlReporter());
}
?>
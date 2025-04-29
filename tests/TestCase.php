<?php

namespace Tests;

use Orkestra\Testing\AbstractTestCase;

abstract class TestCase extends AbstractTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // Reset the session before each test
        unset($_SESSION);
        session_reset();
        session_unset();
        session_write_close();
    }
}

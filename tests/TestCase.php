<?php

namespace Asciisd\Cybersource\Tests;

use Asciisd\Cybersource\CybersourceServiceProvider;
use Orchestra\Testbench\TestCase as OrchestraTestCase;

abstract class TestCase extends OrchestraTestCase
{
    protected function getPackageProviders($app)
    {
        return [
            CybersourceServiceProvider::class,
        ];
    }

    protected function getEnvironmentSetUp($app)
    {
        $app['config']->set('cybersource.access_key', 'test_access_key');
        $app['config']->set('cybersource.profile_id', 'test_profile_id');
        $app['config']->set('cybersource.secret_key', 'test_secret_key');
    }
}

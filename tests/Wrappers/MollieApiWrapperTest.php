<?php

namespace Mollie\Laravel\Tests\Wrappers;

use Mollie\Api\MollieApiClient;
use Mollie\Laravel\Tests\TestCase;
use Mollie\Laravel\Wrappers\MollieApiWrapper;

/**
 * Class MollieApiWrapper
 *
 * @package Mollie\Laravel\Tests\Wrappers
 */
class MollieApiWrapperTest extends TestCase
{
    /**
     * API Client mock.
     *
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $api;

    protected $endpoints = [
        'balances',
        'balanceReports',
        'balanceTransactions',
        'chargebacks',
        'customers',
        'customerPayments',
        'invoices',
        'mandates',
        'methods',
        'profileMethods',
        'mandates',
        'onboarding',
        'orders',
        'organizations',
        'permissions',
        'payments',
        'paymentRefunds',
        'paymentLinks',
        'profiles',
        'refunds',
        'settlements',
        'subscriptions',
        'wallets',
        'clients',
        'organizationPartners',
    ];

    /**
     * @before
     */
    protected function setUpApi()
    {
        $this->api = $this->getMockBuilder(MollieApiClient::class)
            ->disableOriginalConstructor()
            ->getMock();
    }

    public function testConstruct()
    {
        $wrapper = new MollieApiWrapper($this->app['config'], $this->app[MollieApiClient::class]);
        $this->assertInstanceOf(MollieApiWrapper::class, $wrapper);
    }

    public function testApiEndpoint()
    {
        $this->api->expects($this->once())->method('setApiEndpoint');
        $this->api->expects($this->once())->method('getApiEndpoint')->willReturn('/test');

        $wrapper = new MollieApiWrapper($this->app['config'], $this->api);

        $wrapper->setApiEndpoint('/test');
        $this->assertSame('/test', $wrapper->getApiEndpoint());
    }

    public function testSetGoodApiKey()
    {
        $this->api->expects($this->atLeastOnce())->method('setApiKey')->with('test_xxxxxxxxxxxxxxxxxxxxxxxxxxxxxx');

        $wrapper = new MollieApiWrapper($this->app['config'], $this->api);
        $wrapper->setApiKey('test_xxxxxxxxxxxxxxxxxxxxxxxxxxxxxx');
    }

    public function testSetBadApiKey()
    {
        $this->expectException(\Mollie\Api\Exceptions\ApiException::class);
        $this->expectExceptionMessage("Invalid API key: 'live_'. An API key must start with 'test_' or 'live_' and must be at least 30 characters long.");
        $wrapper = new MollieApiWrapper($this->app['config'], $this->app[MollieApiClient::class]);
        $wrapper->setApiKey('live_');
    }

    public function testSetGoodToken()
    {
        $this->api->expects($this->once())->method('setAccessToken')->with('access_xxx');

        $wrapper = new MollieApiWrapper($this->app['config'], $this->api);
        $wrapper->setAccessToken('access_xxx');
    }

    public function testSetBadToken()
    {
        $this->expectException(\Mollie\Api\Exceptions\ApiException::class);
        $this->expectExceptionMessage("Invalid OAuth access token: 'BAD'. An access token must start with 'access_'.");
        $wrapper = new MollieApiWrapper($this->app['config'], $this->app[MollieApiClient::class]);
        $wrapper->setAccessToken('BAD');
    }

    public function testEnableDebugging()
    {
        $this->api->expects($this->once())->method('enableDebugging');

        $wrapper = new MollieApiWrapper($this->app['config'], $this->api);
        $wrapper->enableDebugging();
    }

    public function testDisableDebugging()
    {
        $this->api->expects($this->once())->method('disableDebugging');

        $wrapper = new MollieApiWrapper($this->app['config'], $this->api);
        $wrapper->disableDebugging();
    }

    public function testWrappedEndpoints()
    {
        $client = $this->app[MollieApiClient::class];
        $wrapper = new MollieApiWrapper(
            $this->app['config'],
            $client
        );

        foreach ($this->endpoints as $endpoint) {
            $this->assertWrappedEndpoint($client, $wrapper, $endpoint);
        }
    }

    public function testWrappedPropertyEndpoints()
    {
        $client = $this->app[MollieApiClient::class];
        $wrapper = new MollieApiWrapper(
            $this->app['config'],
            $client
        );

        foreach ($this->endpoints as $endpoint) {
            $this->assertWrappedPropertyEndpoint($client, $wrapper, $endpoint);
        }
    }

    public function testUnknownWrappedEndpoint()
    {
        $client = $this->app[MollieApiClient::class];
        $wrapper = new MollieApiWrapper(
            $this->app['config'],
            $client
        );

        $this->expectException(\Error::class);
        $this->expectExceptionMessage('Call to undefined method Mollie\Laravel\Wrappers\MollieApiWrapper::unknown()');

        $wrapper->unknown();
    }

    public function testUnknownWrappedPropertyEndpoint()
    {
        $client = $this->app[MollieApiClient::class];
        $wrapper = new MollieApiWrapper(
            $this->app['config'],
            $client
        );

        $this->expectException(\Error::class);
        $this->expectExceptionMessage('Mollie\Laravel\Wrappers\MollieApiWrapper has no property or method "unknown".');

        $wrapper->unknown;
    }

    /**
     * Asserts that the referenced wrapper method matches the client attribute
     * I.e. $wrapper->payments() returns the same as $client->payments.
     *
     * @param  MollieApiClient $client
     * @param  MollieApiWrapper $wrapper
     * @param  string $reference
     * @return null
     * @throws ExpectationFailedException
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     */
    protected function assertWrappedEndpoint($client, $wrapper, $reference)
    {
        $this->assertEquals($client->$reference, $wrapper->$reference());
    }

    /**
     * Asserts that the referenced wrapper property matches the client attribute
     * I.e. $wrapper->payments returns the same as $client->payments.
     *
     * @param  MollieApiClient $client
     * @param  MollieApiWrapper $wrapper
     * @param  string $reference
     * @return null
     * @throws ExpectationFailedException
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     */
    protected function assertWrappedPropertyEndpoint($client, $wrapper, $reference)
    {
        $this->assertEquals($client->$reference, $wrapper->$reference);
    }
}

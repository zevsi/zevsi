<?php
/**
 * Refer to LICENSE.txt distributed with the Temando Shipping module for notice of license
 */
namespace Temando\Shipping\Model\ResourceModel\Location;

use Magento\TestFramework\Helper\Bootstrap;
use Temando\Shipping\Model\ResourceModel\Repository\LocationRepositoryInterface;
use Temando\Shipping\Test\Integration\Fixture\ApiTokenFixture;
use Temando\Shipping\Test\Integration\Provider\RestResponseProvider;
use Temando\Shipping\Webservice\HttpClientInterfaceFactory;

/**
 * Temando Location Repository Test
 *
 * @magentoAppIsolation enabled
 * @magentoDataFixture createApiToken
 *
 * @package Temando\Shipping\Test\Integration
 * @author  Christoph Aßmann <christoph.assmann@netresearch.de>
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link    https://www.temando.com/
 */
class LocationRepositoryTest extends \PHPUnit\Framework\TestCase
{
    /**
     * Delegate provisioning of test data to separate class
     * @return string[]
     */
    public function getLocationsResponseDataProvider()
    {
        return RestResponseProvider::getLocationsResponseDataProvider();
    }

    /**
     * delegate fixtures creation to separate class.
     */
    public static function createApiToken()
    {
        ApiTokenFixture::createValidToken();
    }

    /**
     * delegate fixtures rollback to separate class.
     */
    public static function createApiTokenRollback()
    {
        ApiTokenFixture::rollbackToken();
    }

    /**
     * Assert that the location repository returns an empty list (does not crash) when api request fails.
     *
     * @test
     */
    public function apiAccessFails()
    {
        $testAdapter = new \Zend\Http\Client\Adapter\Test();
        $testAdapter->setNextRequestWillFail(true);

        $zendClient = new \Zend\Http\Client();
        $zendClient->setAdapter($testAdapter);

        $httpClient = Bootstrap::getObjectManager()->create(\Temando\Shipping\Webservice\HttpClient::class, [
            'client' => $zendClient,
        ]);

        $clientFactoryMock = $this->getMockBuilder(HttpClientInterfaceFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $clientFactoryMock
            ->expects($this->any())
            ->method('create')
            ->willReturn($httpClient);
        Bootstrap::getObjectManager()->addSharedInstance($clientFactoryMock, HttpClientInterfaceFactory::class);

        /** @var LocationRepositoryInterface $locationRepository */
        $locationRepository = Bootstrap::getObjectManager()->get(LocationRepositoryInterface::class);
        $items = $locationRepository->getList();

        $this->assertInternalType('array', $items);
        $this->assertCount(0, $items);
    }

    /**
     * Assert that the location repository returns an empty list (does not crash) when api returns error response.
     *
     * @test
     *
     * @magentoConfigFixture default/carriers/temando/session_endpoint https://auth.temando.io/v1/
     * @magentoConfigFixture default/carriers/temando/sovereign_endpoint https://foo.temando.io/v1/
     */
    public function apiReturnsError()
    {
        $responseBody = '{"message":"Internal server error"}';
        $testResponse = new \Zend\Http\Response();
        $testResponse->setStatusCode(\Zend\Http\Response::STATUS_CODE_500);
        $testResponse->setContent($responseBody);

        $testAdapter = new \Zend\Http\Client\Adapter\Test();
        $testAdapter->setResponse($testResponse);

        $zendClient = new \Zend\Http\Client();
        $zendClient->setAdapter($testAdapter);

        $httpClient = Bootstrap::getObjectManager()->create(\Temando\Shipping\Webservice\HttpClient::class, [
            'client' => $zendClient,
        ]);

        $clientFactoryMock = $this->getMockBuilder(HttpClientInterfaceFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $clientFactoryMock
            ->expects($this->any())
            ->method('create')
            ->willReturn($httpClient);
        Bootstrap::getObjectManager()->addSharedInstance($clientFactoryMock, HttpClientInterfaceFactory::class);

        /** @var LocationRepositoryInterface $locationRepository */
        $locationRepository = Bootstrap::getObjectManager()->get(LocationRepositoryInterface::class);
        $items = $locationRepository->getList();

        $this->assertInternalType('array', $items);
        $this->assertCount(0, $items);
    }

    /**
     * Assert that the location repository returns an empty list when api result is empty.
     *
     * @test
     *
     * @magentoConfigFixture default/carriers/temando/session_endpoint https://auth.temando.io/v1/
     * @magentoConfigFixture default/carriers/temando/sovereign_endpoint https://foo.temando.io/v1/
     */
    public function apiReturnsEmptyResult()
    {
        $responseBody = '{"data":[]}';
        $testResponse = new \Zend\Http\Response();
        $testResponse->setStatusCode(\Zend\Http\Response::STATUS_CODE_200);
        $testResponse->setContent($responseBody);

        $testAdapter = new \Zend\Http\Client\Adapter\Test();
        $testAdapter->setResponse($testResponse);

        $zendClient = new \Zend\Http\Client();
        $zendClient->setAdapter($testAdapter);

        $httpClient = Bootstrap::getObjectManager()->create(\Temando\Shipping\Webservice\HttpClient::class, [
            'client' => $zendClient,
        ]);

        $clientFactoryMock = $this->getMockBuilder(HttpClientInterfaceFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $clientFactoryMock
            ->expects($this->any())
            ->method('create')
            ->willReturn($httpClient);
        Bootstrap::getObjectManager()->addSharedInstance($clientFactoryMock, HttpClientInterfaceFactory::class);

        /** @var LocationRepositoryInterface $locationRepository */
        $locationRepository = Bootstrap::getObjectManager()->get(LocationRepositoryInterface::class);
        $items = $locationRepository->getList();

        $this->assertInternalType('array', $items);
        $this->assertCount(0, $items);
    }

    /**
     * Assert that a non-empty api response is properly passed through the repository.
     *
     * @test
     * @dataProvider getLocationsResponseDataProvider
     *
     * @magentoConfigFixture default/carriers/temando/session_endpoint https://auth.temando.io/v1/
     * @magentoConfigFixture default/carriers/temando/sovereign_endpoint https://foo.temando.io/v1/
     *
     * @param string $responseBody
     */
    public function getList($responseBody)
    {
        $testResponse = new \Zend\Http\Response();
        $testResponse->setStatusCode(\Zend\Http\Response::STATUS_CODE_200);
        $testResponse->setContent($responseBody);

        $testAdapter = new \Zend\Http\Client\Adapter\Test();
        $testAdapter->setResponse($testResponse);

        $zendClient = new \Zend\Http\Client();
        $zendClient->setAdapter($testAdapter);

        $httpClient = Bootstrap::getObjectManager()->create(\Temando\Shipping\Webservice\HttpClient::class, [
            'client' => $zendClient,
        ]);

        $clientFactoryMock = $this->getMockBuilder(HttpClientInterfaceFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $clientFactoryMock
            ->expects($this->any())
            ->method('create')
            ->willReturn($httpClient);
        Bootstrap::getObjectManager()->addSharedInstance($clientFactoryMock, HttpClientInterfaceFactory::class);

        /** @var LocationRepositoryInterface $locationRepository */
        $locationRepository = Bootstrap::getObjectManager()->get(LocationRepositoryInterface::class);
        $items = $locationRepository->getList();

        $this->assertInternalType('array', $items);
        $this->assertCount(2, $items);
    }
}

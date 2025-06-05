<?php

declare(strict_types=1);

namespace Grin\Module\Test\Api;

use Magento\Framework\Webapi\Rest\Request;
use Magento\TestFramework\TestCase\WebapiAbstract;

/**
 * Checks the grin/stockItems api
 */
class StockItemsTest extends WebapiAbstract
{
    const RESOURCE_PATH = '/V1/grin/stockItems';

    /**
     * @magentoApiDataFixture Magento/Catalog/_files/multiple_products.php
     * @dataProvider searchCriteriaDataProvider
     *
     * @param array $searchCriteria
     * @return void
     */
    public function testGetList(array $searchCriteria)
    {
        $serviceInfo = [
            'rest' => [
                'resourcePath' => self::RESOURCE_PATH . '?' . http_build_query($searchCriteria),
                'httpMethod' => Request::HTTP_METHOD_GET,
            ],
        ];

        $response = $this->_webApiCall($serviceInfo, $searchCriteria);

        $this->assertArrayHasKey('search_criteria', $response);
        $this->assertArrayHasKey('total_count', $response);
        $this->assertArrayHasKey('items', $response);
        $this->assertTrue($response['total_count'] === 1);
        $this->assertTrue(count($response['items']) === 1);
        $this->assertEquals(10, $response['items'][0]['product_id']);
        $this->assertEquals(100, $response['items'][0]['qty']);
    }

    /**
     * @return array
     */
    public function searchCriteriaDataProvider()
    {
        return [
            [
                'searchCriteria' => [
                    'searchCriteria' => [
                        'filter_groups' => [
                            [
                                'filters' => [
                                    [
                                        'field' => 'product_id',
                                        'value' => 10,
                                        'condition_type' => 'eq',
                                    ],
                                ],
                            ],
                        ],
                        'current_page' => 1,
                        'page_size' => 1,
                    ],
                ],
            ]
        ];
    }

    /**
     * @magentoApiDataFixture Magento/Catalog/_files/multiple_products.php
     * @dataProvider searchCriteriaFailDataProvider
     *
     * @param array $searchCriteria
     * @param string $exceptionMessage
     * @return void
     */
    public function testGetListFail(array $searchCriteria, string $exceptionMessage)
    {
        $serviceInfo = [
            'rest' => [
                'resourcePath' => self::RESOURCE_PATH . '?' . http_build_query($searchCriteria),
                'httpMethod' => Request::HTTP_METHOD_GET,
            ],
        ];

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage($exceptionMessage);
        $this->_webApiCall($serviceInfo, $searchCriteria);
    }

    /**
     * @return array
     */
    public function searchCriteriaFailDataProvider()
    {
        return [
            [
                'searchCriteria' => [
                    'searchCriteria' => [
                        'filter_groups' => [
                            [
                                'filters' => [
                                    [
                                        'field' => 'product_id',
                                        'value' => 10,
                                        'condition_type' => 'neq',
                                    ],
                                ],
                            ],
                        ],
                        'current_page' => 1,
                        'page_size' => 1,
                    ],
                ],
                'exceptionMessage' => 'Only \"eq\" and \"in\" condition types are supported',
            ],
            [
                'searchCriteria' => [
                    'searchCriteria' => [
                        'current_page' => 1,
                        'page_size' => 1,
                    ],
                ],
                'exceptionMessage' => 'Please define at least one product filter',
            ],
            [
                'searchCriteria' => [
                    'searchCriteria' => [
                        'filter_groups' => [
                            [
                                'filters' => [
                                    [
                                        'field' => 'name',
                                        'value' => 'test',
                                        'condition_type' => 'neq',
                                    ],
                                ],
                            ],
                        ],
                        'current_page' => 1,
                        'page_size' => 1,
                    ],
                ],
                'exceptionMessage' => 'Only filtering by productId is supported',
            ],
        ];
    }
}

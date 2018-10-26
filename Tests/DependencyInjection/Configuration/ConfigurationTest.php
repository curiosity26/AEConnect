<?php
/**
 * Created by PhpStorm.
 * User: alex.boyce
 * Date: 9/11/18
 * Time: 3:53 PM
 */

namespace AE\ConnectBundle\Tests\DependencyInjection\Configuration;

use AE\ConnectBundle\DependencyInjection\Configuration\Configuration;
use Matthias\SymfonyConfigTest\PhpUnit\ConfigurationTestCaseTrait;
use PHPUnit\Framework\TestCase;

class ConfigurationTest extends TestCase
{
    use ConfigurationTestCaseTrait;

    protected function getConfiguration()
    {
        return new Configuration();
    }

    public function testSingleConnection()
    {
        $this->assertProcessedConfigurationEquals(
            [
                'ae_connect' => [
                    'paths'       => ['%kernel.project_dir%/src/App/Entity'],
                    'connections' => [
                        'default' => [
                            'login'           => [
                                'key'      => 'client_key',
                                'secret'   => 'client_secret',
                                'username' => 'username',
                                'password' => 'password',
                            ],
                            'topics'          => [
                                'TestTopic' => [
                                    'type'   => 'Account',
                                    'filter' => [
                                        'CustomField__c' => 'Seattle',
                                    ],
                                ],
                            ],
                            'platform_events' => [
                                'TestEvent__e',
                            ],
                            'objects'         => [
                                'Account',
                                'CustomObject__c',
                            ],
                            'generic_events'  => [
                                'TestGenericEvent',
                            ],
                        ],
                    ],
                ],
            ],
            [
                'paths'       => ['%kernel.project_dir%/src/App/Entity'],
                'connections' => [
                    'default' => [
                        'is_default'      => true,
                        'login'           => [
                            'key'      => 'client_key',
                            'secret'   => 'client_secret',
                            'username' => 'username',
                            'password' => 'password',
                            'url'      => 'https://login.salesforce.com',
                        ],
                        'topics'          => [
                            'TestTopic' => [
                                'type'   => 'Account',
                                'filter' => [
                                    'CustomField__c' => 'Seattle',
                                ],
                            ],
                        ],
                        'config'          => [
                            'replay_start_id' => -2,
                            'cache'           => [
                                'metadata_provider' => 'ae_connect_metadata',
                            ],
                        ],
                        'platform_events' => [
                            'TestEvent__e',
                        ],
                        'objects'         => [
                            'Account',
                            'CustomObject__c',
                        ],
                        'generic_events'  => [
                            'TestGenericEvent',
                        ],
                    ],
                ],
            ]
        );
    }

    public function testDoubleConnection()
    {
        $this->assertProcessedConfigurationEquals(
            [
                'ae_connect' => [
                    'paths'       => ['%kernel.project_dir%/src/App/Entity'],
                    'connections' => [
                        'default'     => [
                            'login'  => [
                                'key'      => 'client_key',
                                'secret'   => 'client_secret',
                                'username' => 'username',
                                'password' => 'password',
                            ],
                            'topics' => [
                                'TestTopic' => [
                                    'type'   => 'Account',
                                    'filter' => [
                                        'CustomField__c' => 'Seattle',
                                    ],
                                ],
                            ],
                        ],
                        'non_default' => [
                            'is_default' => false,
                            'login'      => [
                                'username' => 'username',
                                'password' => 'password',
                                'url'      => 'https://test.salesforce.com',
                            ],
                            'topics'     => [
                                'TestTopic'  => [
                                    'type'   => 'Account',
                                    'filter' => [
                                        'CustomField__c' => 'Seattle',
                                    ],
                                ],
                                'OtherTopic' => [
                                    'type'   => 'Contact',
                                    'filter' => [
                                        'CustomField__c' => 'Manhattan',
                                    ],
                                ],
                            ],
                            'config'     => [
                                'replay_start_id' => -1,
                                'cache'           => [
                                    'metadata_provider' => 'test_metadata',
                                ],
                            ],
                        ],
                    ],
                ],
            ],
            [
                'paths'       => ['%kernel.project_dir%/src/App/Entity'],
                'connections' => [
                    'default'     => [
                        'is_default'      => true,
                        'login'           => [
                            'key'      => 'client_key',
                            'secret'   => 'client_secret',
                            'username' => 'username',
                            'password' => 'password',
                            'url'      => 'https://login.salesforce.com',
                        ],
                        'topics'          => [
                            'TestTopic' => [
                                'type'   => 'Account',
                                'filter' => [
                                    'CustomField__c' => 'Seattle',
                                ],
                            ],
                        ],
                        'config'          => [
                            'replay_start_id' => -2,
                            'cache'           => [
                                'metadata_provider' => 'ae_connect_metadata',
                            ],
                        ],
                        'platform_events' => [],
                        'objects'         => [],
                        'generic_events'  => [],
                    ],
                    'non_default' => [
                        'is_default'      => false,
                        'login'           => [
                            'username' => 'username',
                            'password' => 'password',
                            'url'      => 'https://test.salesforce.com',
                        ],
                        'topics'          => [
                            'TestTopic'  => [
                                'type'   => 'Account',
                                'filter' => [
                                    'CustomField__c' => 'Seattle',
                                ],
                            ],
                            'OtherTopic' => [
                                'type'   => 'Contact',
                                'filter' => [
                                    'CustomField__c' => 'Manhattan',
                                ],
                            ],
                        ],
                        'config'          => [
                            'replay_start_id' => -1,
                            'cache'           => [
                                'metadata_provider' => 'test_metadata',
                            ],
                        ],
                        'platform_events' => [],
                        'objects'         => [],
                        'generic_events'  => [],
                    ],
                ],
            ]
        );
    }

    public function testInvalidDoubleDefaults()
    {
        $this->assertConfigurationIsInvalid(
            [
                'ae_connect' => [
                    'paths'       => ['%kernel.project_dir%/src/App/Entity'],
                    'connections' => [
                        'default'     => [
                            'login'  => [
                                'key'      => 'client_key',
                                'secret'   => 'client_secret',
                                'username' => 'username',
                                'password' => 'password',
                            ],
                            'topics' => [
                                'TestTopic' => [
                                    'type'   => 'Account',
                                    'filter' => [
                                        'CustomField__c' => 'Seattle',
                                    ],
                                ],
                            ],
                        ],
                        'non_default' => [
                            'login'  => [
                                'key'      => 'client_key',
                                'secret'   => 'client_secret',
                                'username' => 'username',
                                'password' => 'password',
                                'url'      => 'http://test.salesforce.com',
                            ],
                            'topics' => [
                                'TestTopic'  => [
                                    'type'   => 'Account',
                                    'filter' => [
                                        'CustomField__c' => 'Seattle',
                                    ],
                                ],
                                'OtherTopic' => [
                                    'type'   => 'Contact',
                                    'filter' => [
                                        'CustomField__c' => 'Manhattan',
                                    ],
                                ],
                            ],
                            'config' => [
                                'replay_start_id' => -1,
                            ],
                        ],
                    ],
                ],
            ]
        );
    }

    public function testInvalidReplayStart()
    {
        $this->assertConfigurationIsInvalid(
            [
                'ae_connect' => [
                    'paths'       => ['%kernel.project_dir%/src/App/Entity'],
                    'connections' => [
                        'default'     => [
                            'login'  => [
                                'key'      => 'client_key',
                                'secret'   => 'client_secret',
                                'username' => 'username',
                                'password' => 'password',
                            ],
                            'topics' => [
                                'TestTopic' => [
                                    'type'  => 'Account',
                                    'filter' => [
                                        'CustomField__c' => 'Seattle',
                                    ],
                                ],
                            ],
                        ],
                        'non_default' => [
                            'is_default' => false,
                            'login'      => [
                                'key'      => 'client_key',
                                'secret'   => 'client_secret',
                                'username' => 'username',
                                'password' => 'password',
                                'url'      => 'http://test.salesforce.com',
                            ],
                            'topics'     => [
                                'TestTopic'  => [
                                    'type'  => 'Account',
                                    'filter' => [
                                        'CustomField__c' => 'Seattle',
                                    ],
                                ],
                                'OtherTopic' => [
                                    'type'  => 'Contact',
                                    'filter' => [
                                        'CustomField__c' => 'Manhattan',
                                    ],
                                ],
                            ],
                            'config'     => [
                                'replay_start_id' => -4,
                            ],
                        ],
                    ],
                ],
            ]
        );
    }
}

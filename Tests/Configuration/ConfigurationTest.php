<?php
/**
 * Created by PhpStorm.
 * User: alex.boyce
 * Date: 9/11/18
 * Time: 3:53 PM
 */

namespace AE\ConnectBundle\Tests\Configuration;

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
                    'connections' => [
                        'default' => [
                            'url' => 'https://test.my.salesforce.com',
                            'login'  => [
                                'key'      => 'client_key',
                                'secret'   => 'client_secret',
                                'username' => 'username',
                                'password' => 'password',
                            ],
                            'topics' => [
                                'TestTopic' => [
                                    'query'  => 'Select Id, Name, CustomField__c From Account',
                                    'filter' => [
                                        'CustomField__c' => 'Seattle',
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
            [
                'connections' => [
                    'default' => [
                        'is_default' => true,
                        'url' => 'https://test.my.salesforce.com',
                        'login'      => [
                            'key'      => 'client_key',
                            'secret'   => 'client_secret',
                            'username' => 'username',
                            'password' => 'password',
                            'url'      => 'http://login.salesforce.com',
                        ],
                        'topics'     => [
                            'TestTopic' => [
                                'query'                => 'Select Id, Name, CustomField__c From Account',
                                'filter'               => [
                                    'CustomField__c' => 'Seattle',
                                ],
                                'api_version'          => '43.0',
                                'create_if_not_exists' => true,
                                'create'               => true,
                                'update'               => true,
                                'undelete'             => true,
                                'delete'               => true,
                                'notify_for_fields'    => 'Referenced',
                            ],
                        ],
                        'config'     => [
                            'replay_start_id' => -2,
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
                    'connections' => [
                        'default'     => [
                            'url' => 'https://test.my.salesforce.com',
                            'login'  => [
                                'key'      => 'client_key',
                                'secret'   => 'client_secret',
                                'username' => 'username',
                                'password' => 'password',
                            ],
                            'topics' => [
                                'TestTopic' => [
                                    'query'  => 'Select Id, Name, CustomField__c From Account',
                                    'filter' => [
                                        'CustomField__c' => 'Seattle',
                                    ],
                                ],
                            ],
                        ],
                        'non_default' => [
                            'is_default' => false,
                            'url' => 'https://test2.lightning.force.com',
                            'login'      => [
                                'key'      => 'client_key',
                                'secret'   => 'client_secret',
                                'username' => 'username',
                                'password' => 'password',
                                'url'      => 'http://test.salesforce.com',
                            ],
                            'topics'     => [
                                'TestTopic'  => [
                                    'query'  => 'Select Id, Name, CustomField__c From Account',
                                    'filter' => [
                                        'CustomField__c' => 'Seattle',
                                    ],
                                ],
                                'OtherTopic' => [
                                    'query'  => 'Select Id, Name, CustomField__c From Contact',
                                    'filter' => [
                                        'CustomField__c' => 'Manhattan',
                                    ],
                                ],
                            ],
                            'config'     => [
                                'replay_start_id' => -1,
                            ],
                        ],
                    ],
                ],
            ],
            [
                'connections' => [
                    'default'     => [
                        'is_default' => true,
                        'url' => 'https://test.my.salesforce.com',
                        'login'      => [
                            'key'      => 'client_key',
                            'secret'   => 'client_secret',
                            'username' => 'username',
                            'password' => 'password',
                            'url'      => 'http://login.salesforce.com',
                        ],
                        'topics'     => [
                            'TestTopic' => [
                                'query'                => 'Select Id, Name, CustomField__c From Account',
                                'filter'               => [
                                    'CustomField__c' => 'Seattle',
                                ],
                                'api_version'          => '43.0',
                                'create_if_not_exists' => true,
                                'create'               => true,
                                'update'               => true,
                                'undelete'             => true,
                                'delete'               => true,
                                'notify_for_fields'    => 'Referenced',
                            ],
                        ],
                        'config'     => [
                            'replay_start_id' => -2,
                        ],
                    ],
                    'non_default' => [
                        'is_default' => false,
                        'url' => 'https://test2.lightning.force.com',
                        'login'      => [
                            'key'      => 'client_key',
                            'secret'   => 'client_secret',
                            'username' => 'username',
                            'password' => 'password',
                            'url'      => 'http://test.salesforce.com',
                        ],
                        'topics'     => [
                            'TestTopic'  => [
                                'query'                => 'Select Id, Name, CustomField__c From Account',
                                'filter'               => [
                                    'CustomField__c' => 'Seattle',
                                ],
                                'api_version'          => '43.0',
                                'create_if_not_exists' => true,
                                'create'               => true,
                                'update'               => true,
                                'undelete'             => true,
                                'delete'               => true,
                                'notify_for_fields'    => 'Referenced',
                            ],
                            'OtherTopic' => [
                                'query'                => 'Select Id, Name, CustomField__c From Contact',
                                'filter'               => [
                                    'CustomField__c' => 'Manhattan',
                                ],
                                'api_version'          => '43.0',
                                'create_if_not_exists' => true,
                                'create'               => true,
                                'update'               => true,
                                'undelete'             => true,
                                'delete'               => true,
                                'notify_for_fields'    => 'Referenced',
                            ],
                        ],
                        'config'     => [
                            'replay_start_id' => -1,
                        ],
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
                    'connections' => [
                        'default'     => [
                            'url' => 'https://test.my.salesforce.com',
                            'login'  => [
                                'key'      => 'client_key',
                                'secret'   => 'client_secret',
                                'username' => 'username',
                                'password' => 'password',
                            ],
                            'topics' => [
                                'TestTopic' => [
                                    'query'  => 'Select Id, Name, CustomField__c From Account',
                                    'filter' => [
                                        'CustomField__c' => 'Seattle',
                                    ],
                                ],
                            ],
                        ],
                        'non_default' => [
                            'url' => 'https://test2.my.salesforce.com',
                            'login'  => [
                                'key'      => 'client_key',
                                'secret'   => 'client_secret',
                                'username' => 'username',
                                'password' => 'password',
                                'url'      => 'http://test.salesforce.com',
                            ],
                            'topics' => [
                                'TestTopic'  => [
                                    'query'  => 'Select Id, Name, CustomField__c From Account',
                                    'filter' => [
                                        'CustomField__c' => 'Seattle',
                                    ],
                                ],
                                'OtherTopic' => [
                                    'query'  => 'Select Id, Name, CustomField__c From Contact',
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
                    'connections' => [
                        'default'     => [
                            'url' => 'https://test.my.salesforce.com',
                            'login'  => [
                                'key'      => 'client_key',
                                'secret'   => 'client_secret',
                                'username' => 'username',
                                'password' => 'password',
                            ],
                            'topics' => [
                                'TestTopic' => [
                                    'query'  => 'Select Id, Name, CustomField__c From Account',
                                    'filter' => [
                                        'CustomField__c' => 'Seattle',
                                    ],
                                ],
                            ],
                        ],
                        'non_default' => [
                            'is_default' => false,
                            'url' => 'https://test2.my.salesforce.com',
                            'login'      => [
                                'key'      => 'client_key',
                                'secret'   => 'client_secret',
                                'username' => 'username',
                                'password' => 'password',
                                'url'      => 'http://test.salesforce.com',
                            ],
                            'topics'     => [
                                'TestTopic'  => [
                                    'query'  => 'Select Id, Name, CustomField__c From Account',
                                    'filter' => [
                                        'CustomField__c' => 'Seattle',
                                    ],
                                ],
                                'OtherTopic' => [
                                    'query'  => 'Select Id, Name, CustomField__c From Contact',
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

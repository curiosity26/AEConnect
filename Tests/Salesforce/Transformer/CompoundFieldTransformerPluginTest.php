<?php
/**
 * Created by PhpStorm.
 * User: alex.boyce
 * Date: 11/1/18
 * Time: 9:09 AM
 */

namespace AE\ConnectBundle\Tests\Salesforce\Transformer;

use AE\ConnectBundle\Manager\ConnectionManagerInterface;
use AE\ConnectBundle\Salesforce\Transformer\Plugins\CompoundFieldTransformerPlugin;
use AE\ConnectBundle\Salesforce\Transformer\Plugins\TransformerPayload;
use AE\ConnectBundle\Tests\Entity\Contact;
use AE\ConnectBundle\Tests\KernelTestCase;
use AE\SalesforceRestSdk\Model\SObject;
use Symfony\Bridge\Doctrine\RegistryInterface;

class CompoundFieldTransformerPluginTest extends AbstractTransformerTest
{
    public function testOutbound()
    {
        $transformer = new CompoundFieldTransformerPlugin();
        $payload     = $this->createPayload();

        $this->assertTrue($transformer->supports($payload));

        $payload->setDirection(TransformerPayload::OUTBOUND);

        $this->assertFalse($transformer->supports($payload));
    }

    public function testInbound()
    {
        $transformer = new CompoundFieldTransformerPlugin();
        $payload     = $this->createPayload();

        $transformer->transform($payload);

        $this->assertEquals('Bob McGuillicutty', $payload->getValue());
        $this->assertEquals('Bob', $payload->getSObject()->FirstName);
        $this->assertEquals('McGuillicutty', $payload->getSObject()->LastName);
    }

    private function createPayload(): TransformerPayload
    {
        $sobject = new SObject(
            [
                'Type' => 'Contact',
                'Name' => [
                    'FirstName' => 'Bob',
                    'LastName'  => 'McGuillicutty',
                ],
            ]
        );

        $connection    = $this->connectionManager->getConnection();
        $metadata      = $connection->getMetadataRegistry()->findMetadataByClass(Contact::class);
        $fieldMetadata = $metadata->getMetadataForField('Name');
        $classMetadata = $this->registry->getManagerForClass(Contact::class)->getClassMetadata(Contact::class);

        $payload = TransformerPayload::inbound()
                                     ->setValue($sobject->Name)
                                     ->setSObject($sobject)
                                     ->setFieldName('Name')
                                     ->setPropertyName($fieldMetadata->getProperty())
                                     ->setMetadata($metadata)
                                     ->setClassMetadata($classMetadata)
        ;

        return $payload;
    }
}

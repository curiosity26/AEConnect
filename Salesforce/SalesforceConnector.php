<?php
/**
 * Created by PhpStorm.
 * User: alex.boyce
 * Date: 10/2/18
 * Time: 5:13 PM
 */

namespace AE\ConnectBundle\Salesforce;

use AE\ConnectBundle\Salesforce\Inbound\Compiler\EntityCompiler;
use AE\ConnectBundle\Salesforce\Inbound\SalesforceConsumerInterface;
use AE\ConnectBundle\Salesforce\Outbound\Compiler\CompilerResult;
use AE\ConnectBundle\Salesforce\Outbound\Compiler\SObjectCompiler;
use AE\ConnectBundle\Salesforce\Outbound\Enqueue\OutboundProcessor;
use Doctrine\Common\Util\ClassUtils;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping\MappingException;
use Doctrine\ORM\ORMException;
use Enqueue\Client\Message;
use Enqueue\Client\ProducerInterface;
use JMS\Serializer\SerializerInterface;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Symfony\Bridge\Doctrine\RegistryInterface;

class SalesforceConnector implements LoggerAwareInterface
{
    use LoggerAwareTrait;

    /**
     * @var SObjectCompiler
     */
    private $sObjectCompiler;

    /**
     * @var EntityCompiler
     */
    private $entityCompiler;

    /**
     * @var ProducerInterface
     */
    private $producer;

    /**
     * @var SerializerInterface
     */
    private $serializer;

    /**
     * @var RegistryInterface
     */
    private $registry;

    /**
     * @var bool
     */
    private $enabled = true;

    public function __construct(
        ProducerInterface $producer,
        SObjectCompiler $sObjectCompiler,
        EntityCompiler $entityCompiler,
        SerializerInterface $serializer,
        RegistryInterface $registry,
        ?LoggerInterface $logger = null
    ) {
        $this->producer        = $producer;
        $this->sObjectCompiler = $sObjectCompiler;
        $this->entityCompiler  = $entityCompiler;
        $this->serializer      = $serializer;
        $this->registry        = $registry;

        $this->setLogger($logger ?: new NullLogger());
    }

    /**
     * @param $entity
     * @param string $connectionName
     *
     * @return bool
     */
    public function send($entity, string $connectionName = 'default'): bool
    {
        if (!$this->enabled) {
            $this->logger->debug('Connector is disabled for {conn}', ['conn' => $connectionName]);

            return false;
        }

        try {
            $result = $this->sObjectCompiler->compile($entity, $connectionName);
        } catch (\RuntimeException $e) {
            $this->logger->warning($e->getMessage());

            return false;
        }

        return $this->sendCompilerResult($result);
    }

    /**
     * @param CompilerResult $result
     * @param string $connectionName
     *
     * @return bool
     */
    public function sendCompilerResult(CompilerResult $result): bool
    {
        $intent  = $result->getIntent();
        $sObject = $result->getSObject();

        if (CompilerResult::DELETE !== $intent) {
            // If there are no fields other than Id set, don't sync
            $fields = array_diff(array_keys($sObject->getFields()), ['Id']);
            if (empty($fields)) {
                $this->logger->debug(
                    'No fields for object {type} to insert or update for {conn}',
                    [
                        'type' => $sObject->getType(),
                        'conn' => $result->getConnectionName(),
                    ]
                );

                return false;
            }
        }

        $message = new Message(
            $this->serializer->serialize($result, 'json')
        );
        $this->producer->sendEvent(OutboundProcessor::TOPIC, $message);

        return true;
    }

    /**
     * @param $object
     * @param string $intent
     * @param string $connectionName
     * @param bool $validate
     *
     * @return bool
     * @throws MappingException
     * @throws ORMException
     * @throws \Doctrine\Common\Persistence\Mapping\MappingException
     */
    public function receive($object, string $intent, string $connectionName = 'default', $validate = true): bool
    {
        if (!$this->enabled) {
            return false;
        }

        if (!is_array($object)) {
            $object = [$object];
        }

        try {
            $entities = [];
            foreach ($object as $obj) {
                $entities = array_merge($entities, $this->entityCompiler->compile($obj, $connectionName, $validate));
            }
        } catch (\RuntimeException $e) {
            $this->logger->warning($e->getMessage());
            $this->logger->debug($e->getTraceAsString());

            return false;
        }

        // Attempt to save all entities in as few transactions as possible
        $this->saveEntitiesToDB($intent, $entities);

        return true;
    }

    /**
     * @return $this
     */
    public function enable()
    {
        $this->enabled = true;

        return $this;
    }

    /**
     * @return $this
     */
    public function disable()
    {
        $this->enabled = false;

        return $this;
    }

    /**
     * @param string $intent
     * @param $entities
     * @param bool $transactional
     *
     * @throws ORMException
     * @throws \Doctrine\Common\Persistence\Mapping\MappingException
     */
    private function saveEntitiesToDB(string $intent, $entities, bool $transactional = true): void
    {
        /** @var EntityManagerInterface[] $managers */
        $managers = [];

        if (!is_array($entities)) {
            $entities = [$entities];
        }

        $entityMap = [];

        foreach ($entities as $entity) {
            $class = ClassUtils::getClass($entity);

            if (!array_key_exists($class, $managers)) {
                $managers[$class] = $this->registry->getManagerForClass($class);
            }

            $manager = $managers[$class];

            // If an exception is thrown by the entity manager, it can force it to close
            // This is how we can reopen it for future transactions
            if (!$manager->isOpen()) {
                $manager = $managers[$class] = EntityManager::create(
                    $manager->getConnection(),
                    $manager->getConfiguration(),
                    $manager->getEventManager()
                );
            }

            switch ($intent) {
                case SalesforceConsumerInterface::CREATED:
                case SalesforceConsumerInterface::UPDATED:
                case SalesforceConsumerInterface::UNDELETED:
                    $manager->merge($entity);
                    break;
                case SalesforceConsumerInterface::DELETED:
                    $manager->remove($entity);
                    break;
            }

            if ($transactional) {
                // When running transactionally, we need to keep track of things in case of an error
                $entityMap[$class][$intent][] = $entity;
            } else {
                // If not running transactional, flush the entity now
                try {
                    $manager->flush();
                } catch (\Throwable $t) {
                    // If an error occurs, log it and carry on
                    $this->logger->warning($t->getMessage());
                } finally {
                    // Clear memory to prevent buildup
                    $manager->clear($class);
                }
            }

            $this->logger->info('{intent} entity of type {type}', ['intent' => $intent, 'type' => $class]);
        }


        // In a transactional run, run through each of the managers for a class (in case they differ) and flush the
        // contents
        if ($transactional) {
            foreach ($managers as $class => $manager) {// Again, another check to make sure the manager is open
                if (!$manager->isOpen()) {
                    continue;
                }
                try {
                    $manager->transactional(
                        function (EntityManagerInterface $em) use ($class) {
                            $em->flush();
                            $em->clear($class);
                        }
                    );
                } catch (\Throwable $t) {
                    $this->logger->warning($t->getMessage());
                    // Clear the current entity manager to save memory
                    $manager->clear($class);
                    // If a transaction fails, try to save entries one by one
                    if (array_key_exists($class, $entityMap)) {
                        foreach ($entityMap[$class] as $intent => $ens) {
                            $this->saveEntitiesToDB($intent, $ens, false);
                        }
                    }
                } finally {
                    if (array_key_exists($class, $entityMap)) {
                        // Clear entity map to save memory
                        unset($entityMap[$class]);
                    }
                }
            }
        }
    }
}

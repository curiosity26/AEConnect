<?php
/**
 * Created by PhpStorm.
 * User: alex.boyce
 * Date: 10/3/18
 * Time: 4:21 PM
 */

namespace AE\ConnectBundle\Salesforce\Outbound\Enqueue;

use AE\ConnectBundle\Manager\ConnectionManagerInterface;
use AE\ConnectBundle\Salesforce\Outbound\Compiler\CompilerResult;
use AE\ConnectBundle\Salesforce\Outbound\Queue\OutboundQueue;
use Enqueue\Client\TopicSubscriberInterface;
use Enqueue\Consumption\Result;
use Interop\Queue\Context;
use Interop\Queue\Message;
use Interop\Queue\Processor;
use JMS\Serializer\Exception\RuntimeException;
use JMS\Serializer\SerializerInterface;

/**
 * Class OutboundProcessor
 *
 * @package AE\ConnectBundle\Salesforce\Outbound\Enqueue
 */
class OutboundProcessor implements Processor, TopicSubscriberInterface
{

    public const TOPIC = 'ae_connect.outbound';

    /**
     * @var OutboundQueue
     */
    private $queue;

    /**
     * @var SerializerInterface
     */
    private $serializer;

    /**
     * @var ConnectionManagerInterface
     */
    private $connectionManager;

    public function __construct(
        OutboundQueue $queue,
        SerializerInterface $serializer,
        ConnectionManagerInterface $connectionManager
    ) {
        $this->serializer        = $serializer;
        $this->queue             = $queue;
        $this->connectionManager = $connectionManager;
    }

    /**
     * @inheritDoc
     *
     * @param Message $message
     */
    public function process(Message $message, Context $context): string
    {
        try {
            /** @var CompilerResult $payload */
            $payload = $this->serializer->deserialize(
                $message->getBody(),
                CompilerResult::class,
                'json'
            );

            if (!$payload) {
                return Result::REJECT;
            }

            $connection = $this->connectionManager->getConnection($payload->getConnectionName());
            if ($connection->isActive()) {
                $this->queue->add($payload);

                return Result::ACK;
            }
        } catch (RuntimeException $e) {
            return Result::REJECT;
        }

        return Result::REQUEUE;
    }

    /**
     * @inheritDoc
     */
    public static function getSubscribedTopics()
    {
        return [self::TOPIC];
    }
}

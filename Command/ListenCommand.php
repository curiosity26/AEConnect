<?php
/**
 * Created by PhpStorm.
 * User: alex.boyce
 * Date: 10/22/18
 * Time: 3:45 PM
 */

namespace AE\ConnectBundle\Command;

use AE\ConnectBundle\Connection\ConnectionInterface;
use AE\ConnectBundle\Manager\ConnectionManagerInterface;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ListenCommand extends Command implements LoggerAwareInterface
{
    use LoggerAwareTrait;

    /**
     * @var ConnectionManagerInterface
     */
    private $connectionManager;

    public function __construct(ConnectionManagerInterface $connectionManager, ?LoggerInterface $logger = null)
    {
        parent::__construct(null);

        $this->connectionManager = $connectionManager;
        $this->setLogger($logger ?: new NullLogger());
    }

    protected function configure()
    {
        $this->setName('ae_connect:listen')
             ->addArgument(
                 'connectionName',
                 InputArgument::OPTIONAL,
                 'The name of the connection you wish to listen to.',
                 'default'
             )
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $connectionName = $input->getArgument('connectionName');
        $connection     = $this->connectionManager->getConnection($connectionName);

        if (null === $connection) {
            throw new \InvalidArgumentException(sprintf("Could not find any connection named '%s'.", $connectionName));
        }

        if (!$connection->isActive()) {
            throw new \InvalidArgumentException(
                sprintf(
                    "Connection, '%s', is inactive, most likely due to bad credentials.",
                    $connectionName
                )
            );
        }

        $output->writeln('<info>Listening to connection: '.$connectionName.'</info>');

        while ($this->start($connection)) {
            $output->writeln('<info>Restarting connection: '.$connectionName.'</info>');
        }
    }

    private function start(ConnectionInterface $connection)
    {
        try {
            $connection->getStreamingClient()->start();
        } catch (\Exception $e) {
            $this->logger->critical($e->getMessage());
            $connection->getStreamingClient()->stop();
        }

        return true;
    }
}

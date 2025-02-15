<?php
/**
 * Created by PhpStorm.
 * User: alex.boyce
 * Date: 9/11/18
 * Time: 5:59 PM
 */

namespace AE\ConnectBundle\Manager;

use AE\ConnectBundle\Connection\ConnectionInterface;
use Doctrine\Common\Collections\ArrayCollection;

class ConnectionManager implements ConnectionManagerInterface
{
    /**
     * @var ArrayCollection
     */
    private $connections;

    public function __construct()
    {
        $this->connections = new ArrayCollection();
    }

    /**
     * @param string $name
     * @param ConnectionInterface $connection
     *
     * @return ConnectionManager
     */
    public function registerConnection(ConnectionInterface $connection): ConnectionManager
    {
        $this->connections->set($connection->getName(), $connection);

        return $this;
    }

    /**
     * @param null|string $name
     *
     * @return ConnectionInterface|null
     */
    public function getConnection(?string $name = null): ?ConnectionInterface
    {
        if (null === $name || 'default' === $name) {
            if ($this->connections->containsKey('default')) {
                return $this->connections->get('default');
            }

            /** @var ConnectionInterface $connection */
            foreach ($this->connections as $connection) {
                if ($connection->isDefault()) {
                    return $connection;
                }
            }
        }

        return $this->connections->get($name);
    }

    /**
     * @return array
     */
    public function getConnections(): array
    {
        return $this->connections->toArray();
    }
}

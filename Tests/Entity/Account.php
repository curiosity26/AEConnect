<?php
/**
 * Created by PhpStorm.
 * User: alex.boyce
 * Date: 10/2/18
 * Time: 1:34 PM
 */

namespace AE\ConnectBundle\Tests\Entity;

use AE\ConnectBundle\Annotations as AEConnect;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

/**
 * Class Account
 * @AEConnect\SObjectType(name="Account", connections={"*"})
 * @AEConnect\RecordType("Client", connections={"db_test"})
 * @ORM\Entity()
 * @ORM\Table("account")
 */
class Account
{
    /**
     * @var int
     * @ORM\Id()
     * @ORM\Column(type="integer", options={"unsigned"=true}, unique=true, nullable=false)
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var Uuid
     * @AEConnect\Field("S3F__hcid__c", connections={"default"})
     * @AEConnect\Field("AE_Connect_Id__c", connections={"db_test"})
     * @AEConnect\ExternalId()
     * @ORM\Column(type="uuid", nullable=false, unique=true)
     */
    private $extId;

    /**
     * @var string
     * @AEConnect\Field("Name", connections={"*"})
     * @ORM\Column(length=80, nullable=false)
     */
    private $name;

    /**
     * @var array
     * @ORM\Column(type="array")
     * @AEConnect\Field("S3F__Test_Picklist__c", connections={"default"})
     */
    private $testPicklist;

    /**
     * @var string
     * @AEConnect\SalesforceId(connection="default")
     * @ORM\Column(length=18, nullable=true, unique=true)
     */
    private $sfid;

    /**
     * @var string
     * @AEConnect\Connection(connections={"default"})
     * @ORM\Column(length=40, nullable=true)
     */
    private $connection;

    /**
     * @var OrgConnection[]|Collection|array
     * @ORM\ManyToMany(targetEntity="AE\ConnectBundle\Tests\Entity\OrgConnection", cascade={"persist", "merge"})
     * @AEConnect\Connection(connections={"db_test"})
     */
    private $connections;

    /**
     * @var SalesforceId[]|Collection|array
     * @ORM\ManyToMany(targetEntity="AE\ConnectBundle\Tests\Entity\SalesforceId",
     *     cascade={"persist", "merge", "remove"},
     *     orphanRemoval=true
     *     )
     * @AEConnect\SalesforceId(connection="db_test")
     */
    private $sfids;

    /**
     * @var \DateTimeInterface|null
     * @ORM\Column(type="datetimetz", nullable=true)
     */
    private $createdDate;

    public function __construct()
    {
        $this->connections = new ArrayCollection();
        $this->sfids       = new ArrayCollection();
    }

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param mixed $id
     *
     * @return Account
     */
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * @return UuidInterface
     */
    public function getExtId(): UuidInterface
    {
        return $this->extId;
    }

    /**
     * @param UuidInterface $extId
     *
     * @return Account
     */
    public function setExtId(UuidInterface $extId): Account
    {
        $this->extId = $extId;

        return $this;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param string $name
     *
     * @return Account
     */
    public function setName(string $name): Account
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return array
     */
    public function getTestPicklist(): array
    {
        return $this->testPicklist;
    }

    /**
     * @param array $testPicklist
     *
     * @return Account
     */
    public function setTestPicklist(array $testPicklist): Account
    {
        $this->testPicklist = $testPicklist;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getSfid()
    {
        return $this->sfid;
    }

    /**
     * @param string $sfid
     *
     * @return Account
     */
    public function setSfid($sfid): Account
    {
        $this->sfid = $sfid;

        return $this;
    }

    /**
     * @return string
     */
    public function getConnection(): ?string
    {
        return $this->connection;
    }

    /**
     * @param string $connection
     *
     * @return Account
     */
    public function setConnection(?string $connection): Account
    {
        $this->connection = $connection;

        return $this;
    }

    /**
     * @return OrgConnection[]|array|Collection
     */
    public function getConnections()
    {
        return $this->connections;
    }

    /**
     * @param OrgConnection[]|array|Collection $connections
     *
     * @return Account
     */
    public function setConnections($connections)
    {
        $this->connections = $connections;

        return $this;
    }

    /**
     * @return SalesforceId[]|array|Collection
     */
    public function getSfids()
    {
        return $this->sfids;
    }

    /**
     * @param SalesforceId[]|array|Collection $sfids
     *
     * @return Account
     */
    public function setSfids($sfids)
    {
        $this->sfids = $sfids;

        return $this;
    }

    /**
     * @return \DateTimeInterface|null
     */
    public function getCreatedDate(): ?\DateTimeInterface
    {
        return $this->createdDate;
    }

    /**
     * @param \DateTimeInterface|null $createdDate
     * @AEConnect\Field("CreatedDate", connections={"default"})
     *
     * @return Account
     */
    public function setCreatedDate(?\DateTimeInterface $createdDate): Account
    {
        $this->createdDate = $createdDate;

        return $this;
    }
}

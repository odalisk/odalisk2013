<?php

namespace OdaliskProject\Bundle\Entity;

use Doctrine\ORM\Mapping as ORM;

use Doctrine\Common\Collections\ArrayCollection;

use Gedmo\Mapping\Annotation as Gedmo;

/**
 * OdaliskProject\Bundle\Portal
 *
 * @ORM\Table(name="portals")
 * @ORM\Entity(repositoryClass="OdaliskProject\Bundle\Repository\PortalRepository")
 */
class Portal
{
    /**
     * @var integer $id
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @var string $name
     *
     * @ORM\Column(name="name", type="string", length=255)
     */
    protected $name;

    /**
     * @var string $url
     *
     * @ORM\Column(name="url", type="string", length=255)
     */
    protected $url;


    /**
     * @var string $country
     *
     * @ORM\Column(name="country", type="string", length=255)
     */
    protected $country;

    /**
     * @var string $entity
     *
     * @ORM\Column(name="entity", type="string", length=255)
     */
    protected $entity;

    /**
     * @var string $status
     *
     * @ORM\Column(name="status", type="string", length=255)
     */
    protected $status;
    
    /**
     * @var string $type
     *
     * @ORM\Column(name="type", type="string", length=255)
     */
    protected $type;
    
    /**
     * @var int $dataset_count
     *
     * @ORM\Column(name="dataset_count", type="integer", nullable=true)
     */
    protected $dataset_count;

    /**
     * @var string $created_at When did we create this record
     *
     * @ORM\Column(name="created_at", type="datetime",nullable=true)
     * @Gedmo\Timestampable(on="create")
     */
    protected $created_at;

    /**
     * @var string $updated_at When did we update this record
     *
     * @ORM\Column(name="updated_at", type="datetime",nullable=true)
     * @Gedmo\Timestampable(on="update")
     */
    protected $updated_at;

    /**
     * @ORM\OneToMany(targetEntity="Dataset", mappedBy="portal")
     */
    protected $datasets;

    /**
     * @ORM\OneToOne(targetEntity="OdaliskProject\Bundle\Entity\PortalCriteria")
     * @ORM\JoinColumn(name="criteria_id", referencedColumnName="id", onDelete="CASCADE")
     */
    protected $criteria;

    /**
     * @ORM\OneToOne(targetEntity="OdaliskProject\Bundle\Entity\Metric")
     * @ORM\JoinColumn(name="metric_id", referencedColumnName="id", onDelete="CASCADE")
     */
    protected $metric;

    public function __construct()
    {
        $this->datasets = new ArrayCollection();
    }

    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set name
     *
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * Get name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set country
     *
     * @param string $country
     */
    public function setCountry($country)
    {
        $this->country = $country;
    }

    /**
     * Get country
     *
     * @return string
     */
    public function getCountry()
    {
        return $this->country;
    }

    /**
     * Set entity
     *
     * @param string $entity
     */
    public function setEntity($entity)
    {
        $this->entity = $entity;
    }

    /**
     * Get entity
     *
     * @return string
     */
    public function getEntity()
    {
        return $this->entity;
    }

    /**
     * Set status
     *
     * @param string $status
     */
    public function setStatus($status)
    {
        $this->status = $status;
    }

    /**
     * Get status
     *
     * @return string
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * Set url
     *
     * @param string $url
     */
    public function setUrl($url)
    {
        $this->url = $url;
    }

    /**
     * Get url
     *
     * @return string
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * Set base_url
     *
     * @param string $baseUrl
     */
    public function setBaseUrl($baseUrl)
    {
        $this->base_url = $baseUrl;
    }

    /**
     * Get base_url
     *
     * @return string
     */
    public function getBaseUrl()
    {
        return $this->base_url;
    }

    /**
     * Add datasets
     *
     * @param OdaliskProject\Bundle\Entity\Dataset $dataset
     */
    public function addDataset(\OdaliskProject\Bundle\Entity\Dataset $dataset)
    {
        if (!$this->datasets->contains($dataset)) {
            $this->datasets[] = $dataset;
            $dataset->setPortal($this);
        }
    }

    /**
     * Get datasets
     *
     * @return Doctrine\Common\Collections\Collection
     */
    public function getDatasets()
    {
        return $this->datasets;
    }

    /**
     * Set created_at
     *
     * @param datetime $createdAt
     */
    public function setCreatedAt($createdAt)
    {
        $this->created_at = $createdAt;
    }

    /**
     * Get created_at
     *
     * @return datetime
     */
    public function getCreatedAt()
    {
        return $this->created_at;
    }

    /**
     * Set updated_at
     *
     * @param datetime $updatedAt
     */
    public function setUpdatedAt($updatedAt)
    {
        $this->updated_at = $updatedAt;
    }

    /**
     * Get updated_at
     *
     * @return datetime
     */
    public function getUpdatedAt()
    {
        return $this->updated_at;
    }

    /**
     * Set criteria
     *
     * @param OdaliskProject\Bundle\Entity\PortalCriteria $criteria
     */
    public function setCriteria(\OdaliskProject\Bundle\Entity\PortalCriteria $criteria)
    {
        $this->criteria = $criteria;
    }

    /**
     * Get criteria
     *
     * @return OdaliskProject\Bundle\Entity\PortalCriteria 
     */
    public function getCriteria()
    {
        return $this->criteria;
    }

    /**
     * Set type
     *
     * @param string $type
     */
    public function setType($type)
    {
        $this->type = $type;
    }

    /**
     * Get type
     *
     * @return string 
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Set dataset_count
     *
     * @param integer $datasetCount
     */
    public function setDatasetCount($datasetCount)
    {
        $this->dataset_count = $datasetCount;
    }

    /**
     * Get dataset_count
     *
     * @return integer 
     */
    public function getDatasetCount()
    {
        return $this->dataset_count;
    }

    /**
     * Set metric
     *
     * @param OdaliskProject\Bundle\Entity\Metric $metric
     */
    public function setMetric(\OdaliskProject\Bundle\Entity\Metric $metric)
    {
        $this->metric = $metric;
    }

    /**
     * Get metric
     *
     * @return OdaliskProject\Bundle\Entity\Metric 
     */
    public function getMetric()
    {
        return $this->metric;
    }
}
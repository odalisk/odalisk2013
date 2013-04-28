<?php

namespace OdaliskProject\Bundle\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as MongoDB;

/** @MongoDB\Document */
class DcatDataset
{
    /** @MongoDB\Id */
    private $id;

    /** @MongoDB\Field */
    private $portalName;

    /** @MongoDB\File */
    private $file;

    /** @MongoDB\Field */
    private $uploadDate;

    /** @MongoDB\Field */
    private $length;

    /** @MongoDB\Field */
    private $chunkSize;

    /** @MongoDB\Field */
    private $md5;



    public function getId()
    {
        return $this->id;
    }

    public function setPortalName($portalName)
    {
        $this->portalName = $portalName;
    }

    public function getPortalName()
    {
        return $this->portalName;
    }

    public function getFile()
    {
        return $this->file;
    }

    public function setFile($file)
    {
        $this->file = $file;
    }
}
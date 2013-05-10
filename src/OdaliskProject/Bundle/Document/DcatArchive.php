<?php

namespace OdaliskProject\Bundle\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as MongoDB;

/** @MongoDB\Document */
class DcatArchive
{
    /** @MongoDB\Id */
    private $id;

    /** @MongoDB\Field */
    private $name;

    /** @MongoDB\Field */
    private $archiveName;

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

    public function setArchiveName($archiveName)
    {
        $this->archiveName = $archiveName;
    }

    public function getArchiveName()
    {
        return $this->archiveName;
    }

    public function setName($name)
    {
        $this->name = $name;
    }

    public function getName()
    {
        return $this->name;
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
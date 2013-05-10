<?php

namespace OdaliskProject\Bundle\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as MongoDB;

/** @MongoDB\Document */
class DcatCatalog
{
    /** @MongoDB\Id */
    private $id;

    /** @MongoDB\Field */
    private $name;

    /** @MongoDB\Field */
    private $key;

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

    public function setKey($key)
    {
        $this->key = $key;
    }

    public function getKey()
    {
        return $this->key;
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
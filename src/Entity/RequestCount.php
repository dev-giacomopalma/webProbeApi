<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity
 * @ORM\Table(name="requestCount")
 */
class RequestCount {
    /**
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;
    /**
     * @ORM\Column(type="string", length=100)
     * @Assert\NotBlank()
     *
     */
    private $username;
    /**
     * @ORM\Column(type="integer")
     * @Assert\NotBlank()
     */
    private $count;
    /**
     * @ORM\Column(type="datetime")
     * @Assert\NotBlank()
     *
     */
    private $lastRequestDate;

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }
    /**
     * @param mixed $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * @return mixed
     */
    public function getUsername()
    {
        return $this->username;
    }

    /**
     * @param mixed $username
     */
    public function setUsername($username): void
    {
        $this->username = $username;
    }

    /**
     * @return mixed
     */
    public function getCount()
    {
        return $this->count;
    }

    /**
     * @param mixed $count
     */
    public function setCount($count): void
    {
        $this->count = $count;
    }

    /**
     * @return mixed
     */
    public function getLastRequestDate()
    {
        return $this->lastRequestDate;
    }

    /**
     * @param mixed $lastRequestDate
     */
    public function setLastRequestDate($lastRequestDate): void
    {
        $this->lastRequestDate = $lastRequestDate;
    }

}
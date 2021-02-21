<?php

namespace App\Entity;

use App\Repository\SongRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=SongRepository::class)
 */
class Song
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $name;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $author;

    /**
     * @ORM\Column(type="text")
     */
    private $body;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $capo;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $madeBy;

    /**
     * @ORM\Column(type="json", nullable=true)
     */
    private $revision = [];

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getAuthor(): ?string
    {
        return $this->author;
    }

    public function setAuthor(string $author): self
    {
        $this->author = $author;

        return $this;
    }

    public function getBody(): ?string
    {
        return $this->body;
    }

    public function setBody(string $body): self
    {
        $this->body = $body;

        return $this;
    }

    public function getCapo(): ?int
    {
        return $this->capo;
    }

    public function setCapo(?int $capo): self
    {
        $this->capo = $capo;

        return $this;
    }

    public function getMadeBy(): ?string
    {
        return $this->madeBy;
    }

    public function setMadeBy(string $madeBy): self
    {
        $this->madeBy = $madeBy;

        return $this;
    }

    public function getRevision(): ?array
    {
        return $this->revision;
    }

    public function setRevision(?array $revision): self
    {
        $this->revision = $revision;

        return $this;
    }
}

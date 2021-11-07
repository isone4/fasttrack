<?php

namespace App\Entity;

use App\Repository\CodeRepoRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=CodeRepoRepository::class)
 */
class CodeRepo
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
    private $reponame;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $orgname;

    /**
     * @ORM\Column(type="integer")
     */
    private $trustpoints = 0;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $url;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $provider;

    /**
     * @ORM\Column(type="date")
     */
    private $creationdate;

    /**
     * @ORM\Column(type="string")
     */
    private $externalId;

    public function __construct(string $externalId, string $orgname, string $reponame, string $url, string $provider, \DateTimeImmutable $creationdate)
    {
        $this->externalId = $externalId;
        $this->orgname = $orgname;
        $this->reponame = $reponame;
        $this->url = $url;
        $this->provider = $provider;
        $this->creationdate = $creationdate;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getReponame(): ?string
    {
        return $this->reponame;
    }

    public function setReponame(string $reponame): self
    {
        $this->reponame = $reponame;

        return $this;
    }

    public function getOrgname(): ?string
    {
        return $this->orgname;
    }

    public function setOrgname(string $orgname): self
    {
        $this->orgname = $orgname;

        return $this;
    }

    public function getTrustpoints(): ?int
    {
        return $this->trustpoints;
    }

    public function setTrustpoints(int $trustpoints): self
    {
        $this->trustpoints = $trustpoints;

        return $this;
    }

    public function getUrl(): ?string
    {
        return $this->url;
    }

    public function setUrl(string $url): self
    {
        $this->url = $url;

        return $this;
    }

    public function getProvider(): ?string
    {
        return $this->provider;
    }

    public function setProvider(string $provider): self
    {
        $this->provider = $provider;

        return $this;
    }

    public function getCreationdate(): ?\DateTimeInterface
    {
        return $this->creationdate;
    }

    public function setCreationdate(\DateTimeInterface $creationdate): self
    {
        $this->creationdate = $creationdate;

        return $this;
    }

    public function getExternalId(): ?string
    {
        return $this->externalId;
    }

    public function setExternalId(string $externalId): self
    {
        $this->externalId = $externalId;

        return $this;
    }
}

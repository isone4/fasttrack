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
    private $trustpoints;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $url;

    public function __construct(string $orgname, string $reponame, string $trustpoints, string $url)
    {
        $this->orgname = $orgname;
        $this->reponame = $reponame;
        $this->trustpoints = $trustpoints;
        $this->url = $url;
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
}

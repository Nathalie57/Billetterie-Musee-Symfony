<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\TicketRepository")
 */
class Ticket
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=32)
     */
    private $visitorName;

    /**
     * @ORM\Column(type="date")
     */
    private $visitorBirthday;

    /**
     * @ORM\Column(type="boolean")
     */
    private $reduction;

    /**
     * @ORM\Column(type="float")
     */
    private $price;

    /**
     * @ORM\Column(type="string", length=32)
     */
    private $country;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User", inversedBy="tickets")
     * @ORM\JoinColumn(nullable=false)
     */
    private $idOrder;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getVisitorName(): ?string
    {
        return $this->visitorName;
    }

    public function setVisitorName(string $visitorName): self
    {
        $this->visitorName = $visitorName;

        return $this;
    }

    public function getVisitorBirthday(): ?\DateTimeInterface
    {
        return $this->visitorBirthday;
    }

    public function setVisitorBirthday(\DateTimeInterface $visitorBirthday): self
    {
        $this->visitorBirthday = $visitorBirthday;

        return $this;
    }

    public function getReduction(): ?bool
    {
        return $this->reduction;
    }

    public function setReduction(bool $reduction): self
    {
        $this->reduction = $reduction;

        return $this;
    }

    public function getPrice(): ?float
    {
        return $this->price;
    }

    public function setPrice(float $price): self
    {
        $this->price = $price;

        return $this;
    }

    public function getCountry(): ?string
    {
        return $this->country;
    }

    public function setCountry(string $country): self
    {
        $this->country = $country;

        return $this;
    }

    public function getIdOrder(): ?User
    {
        return $this->idOrder;
    }

    public function setIdOrder(?User $idOrder): self
    {
        $this->idOrder = $idOrder;

        return $this;
    }
}

<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\UserRepository")
 */
class User
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="guid")
     */
    private $orderCode;

    /**
     * @ORM\Column(type="date")
     */
    private $orderDate;

    /**
     * @ORM\Column(type="integer")
     */
    private $numberTickets;

    /**
     * @ORM\Column(type="string", length=32)
     */
    private $clientName;

    /**
     * @ORM\Column(type="string", length=128)
     */
    private $clientAddress;

    /**
     * @ORM\Column(type="string", length=32)
     */
    private $clientCountry;

    /**
     * @ORM\Column(type="string", length=64)
     */
    private $clientEmail;

    /**
     * @ORM\Column(type="date")
     */
    private $visitDate;

    /**
     * @ORM\Column(type="decimal", precision=2, scale=1)
     */
    private $visitDuration;

    /**
     * @ORM\Column(type="float")
     */
    private $totalPrice;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Ticket", mappedBy="idOrder", orphanRemoval=true)
     */
    private $tickets;

    /**
     * @ORM\Column(type="string", length=255)
     */

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getOrderCode(): ?string
    {
        return $this->orderCode;
    }

    public function setOrderCode(string $orderCode): self
    {
        $this->orderCode = $orderCode;

        return $this;
    }

    public function getOrderDate(): ?\DateTimeInterface
    {
        return $this->orderDate;
    }

    public function setOrderDate(\DateTimeInterface $orderDate): self
    {
        $this->orderDate = $orderDate;

        return $this;
    }

    public function getNumberTickets(): ?int
    {
        return $this->numberTickets;
    }

    public function setNumberTickets(int $numberTickets): self
    {
        $this->numberTickets = $numberTickets;

        return $this;
    }

    public function getClientName(): ?string
    {
        return $this->clientName;
    }

    public function setClientName(string $clientName): self
    {
        $this->clientName = $clientName;

        return $this;
    }

    public function getClientAddress(): ?string
    {
        return $this->clientAddress;
    }

    public function setClientAddress(string $clientAddress): self
    {
        $this->clientAddress = $clientAddress;

        return $this;
    }

    public function getClientCountry(): ?string
    {
        return $this->clientCountry;
    }

    public function setClientCountry(string $clientCountry): self
    {
        $this->clientCountry = $clientCountry;

        return $this;
    }

    public function getClientEmail(): ?string
    {
        return $this->clientEmail;
    }

    public function setClientEmail(string $clientEmail): self
    {
        $this->clientEmail = $clientEmail;

        return $this;
    }

    public function getVisitDate(): ?\DateTimeInterface
    {
        return $this->visitDate;
    }

    public function setVisitDate(\DateTimeInterface $visitDate): self
    {
        $this->visitDate = $visitDate;

        return $this;
    }

    public function getVisitDuration(): ?string
    {
        return $this->visitDuration;
    }

    public function setVisitDuration(string $visitDuration): self
    {
        $this->visitDuration = $visitDuration;

        return $this;
    }

    public function getTotalPrice(): ?float
    {
        return $this->totalPrice;
    }

    public function setTotalPrice(float $totalPrice): self
    {
        $this->totalPrice = $totalPrice;

        return $this;
    }

    /**
     * @return Collection|Ticket[]
     */
    public function getTickets(): Collection
    {
        return $this->tickets;
    }

    public function addTicket(Ticket $ticket): self
    {
        if (!$this->tickets->contains($ticket)) {
            $this->tickets[] = $ticket;
            $ticket->setIdOrder($this);
        }

        return $this;
    }

    public function removeTicket(Ticket $ticket): self
    {
        if ($this->tickets->contains($ticket)) {
            $this->tickets->removeElement($ticket);
            // set the owning side to null (unless already changed)
            if ($ticket->getIdOrder() === $this) {
                $ticket->setIdOrder(null);
            }
        }

        return $this;
    }
}

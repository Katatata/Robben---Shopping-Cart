<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\InvoiceRepository")
 */
class Invoice
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User", inversedBy="Invoices")
     */
    private $User_ID;

    /**
     * @ORM\Column(type="integer")
     */
    private $Tax;

    /**
     * @ORM\Column(type="date")
     */
    private $Date;

    /**
     * @ORM\Column(type="decimal", scale=2, nullable=true)
     */
    private $Total_price;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Bestelling", mappedBy="invoice_id")
     */
    private $bestelling_id;

    public function __construct()
    {
        $this->bestelling_id = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUserID(): ?User
    {
        return $this->User_ID;
    }

    public function setUserID(?User $User_ID): self
    {
        $this->User_ID = $User_ID;

        return $this;
    }

    public function getTax(): ?int
    {
        return $this->Tax;
    }

    public function setTax(int $Tax): self
    {
        $this->Tax = $Tax;

        return $this;
    }

    public function getDate(): ?\DateTimeInterface
    {
        return $this->Date;
    }

    public function setDate(\DateTimeInterface $Date): self
    {
        $this->Date = $Date;

        return $this;
    }

    public function getTotalPrice(): ?string
    {
        return $this->Total_price;
    }

    public function setTotalPrice(?string $Total_price): self
    {
        $this->Total_price = $Total_price;

        return $this;
    }

    /**
     * @return Collection|Bestelling[]
     */
    public function getBestellingId(): Collection
    {
        return $this->bestelling_id;
    }

    public function addBestellingId(Bestelling $bestellingId): self
    {
        if (!$this->bestelling_id->contains($bestellingId)) {
            $this->bestelling_id[] = $bestellingId;
            $bestellingId->setInvoiceId($this);
        }

        return $this;
    }

    public function removeBestellingId(Bestelling $bestellingId): self
    {
        if ($this->bestelling_id->contains($bestellingId)) {
            $this->bestelling_id->removeElement($bestellingId);
            // set the owning side to null (unless already changed)
            if ($bestellingId->getInvoiceId() === $this) {
                $bestellingId->setInvoiceId(null);
            }
        }

        return $this;
    }

}

<?php
// src/Entity/User.php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use FOS\UserBundle\Model\User as BaseUser;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="fos_user")
 */
class User extends BaseUser
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Invoice", mappedBy="User_ID")
     */
    private $Invoices;

    public function __construct()
    {
        parent::__construct();
        $this->Invoices = new ArrayCollection();
        // your own logic
    }

    /**
     * @return Collection|Invoice[]
     */
    public function getInvoices(): Collection
    {
        return $this->Invoices;
    }

    public function addInvoice(Invoice $invoice): self
    {
        if (!$this->Invoices->contains($invoice)) {
            $this->Invoices[] = $invoice;
            $invoice->setUserID($this);
        }

        return $this;
    }

    public function removeInvoice(Invoice $invoice): self
    {
        if ($this->Invoices->contains($invoice)) {
            $this->Invoices->removeElement($invoice);
            // set the owning side to null (unless already changed)
            if ($invoice->getUserID() === $this) {
                $invoice->setUserID(null);
            }
        }

        return $this;
    }
}
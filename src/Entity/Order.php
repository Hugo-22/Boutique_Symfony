<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\OrderRepository")
 * @ORM\Table(name="`order`")
 */
class Order
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="datetime")
     */
    private $date;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\OrderDetail", mappedBy="orderId")
     */
    private $orderDetailsId;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User", inversedBy="orders")
     * @ORM\JoinColumn(nullable=false)
     */
    private $user;

    /**
     * @ORM\Column(type="float")
     */
    private $price;

    public function __construct()
    {
        $this->orderDetails = new ArrayCollection();
        $this->orderDetailsId = new ArrayCollection();
    }

    
    public function getId(): ?int
    {
        return $this->id;
    }

    public function __toString()
    {
        return $this->id;
    }

    public function getDate(): ?\DateTimeInterface
    {
        return $this->date;
    }

    public function setDate(\DateTimeInterface $date): self
    {
        $this->date = $date;

        return $this;
    }


    /**
     * @return Collection|OrderDetail[]
     */
    public function getOrderDetailsId(): Collection
    {
        return $this->orderDetailsId;
    }

    public function addOrderDetailsId(OrderDetail $orderDetailsId): self
    {
        if (!$this->orderDetailsId->contains($orderDetailsId)) {
            $this->orderDetailsId[] = $orderDetailsId;
            $orderDetailsId->setOrderId($this);
        }

        return $this;
    }

    public function removeOrderDetailsId(OrderDetail $orderDetailsId): self
    {
        if ($this->orderDetailsId->contains($orderDetailsId)) {
            $this->orderDetailsId->removeElement($orderDetailsId);
            // set the owning side to null (unless already changed)
            if ($orderDetailsId->getOrderId() === $this) {
                $orderDetailsId->setOrderId(null);
            }
        }

        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): self
    {
        $this->user = $user;

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

}

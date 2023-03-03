<?php

namespace Eccube\Entity;


use Doctrine\ORM\Mapping as ORM;
use Eccube\Service\Calculator\OrderItemCollection;
use Eccube\Service\PurchaseFlow\ItemCollection;
/**
 * @ORM\Entity(repositoryClass="Eccube\Repository\SimNumberARepository")
 */
class SimNumberA
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=14)
     */
    private $number;

    /**
     * @ORM\Column(type="decimal", precision=10, scale=0)
     */
    private $price;

    /**
     * @ORM\Column(type="string",  length=666, nullable=true)
     */
    private $product_code;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNumber(): ?string
    {
        return $this->number;
    }

    public function setNumber(string $number): self
    {
        $this->number = $number;

        return $this;
    }

    public function getPrice(): ?string
    {
        return $this->price;
    }

    public function setPrice(string $price): self
    {
        $this->price = $price;

        return $this;
    }
}

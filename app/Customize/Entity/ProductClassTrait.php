<?php


namespace Customize\Entity;
use Eccube\Annotation\EntityExtension;

/**
 * @EntityExtension("Eccube\Entity\ProductClass")
 */
Trait ProductClassTrait
{
    /**
     * One Product has One MstProduct.
     * @var \Customize\Entity\MstProduct
     *
     * @ORM\OneToOne(targetEntity="Customize\Entity\MstProduct")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="product_code", referencedColumnName="product_code")
     * })
     */
    private $MstProduct;

    /**
     * @return \Customize\Entity\MstProduct
     */
    public function getMstProduct()
    {
        return $this->MstProduct;
    }

    /**
     * @param mixed $MstProduct
     */
    public function setMstProduct($MstProduct): void
    {
        $this->MstProduct = $MstProduct;
    }
}

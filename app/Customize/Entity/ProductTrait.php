<?php


namespace Customize\Entity;

use Doctrine\ORM\Mapping as ORM;
use Eccube\Annotation\EntityExtension;
use Eccube\Entity\Product;

/**
 * @EntityExtension("Eccube\Entity\Product")
 */
trait ProductTrait
{

    /**
     * @var \Customize\Entity\MstProduct
     * @ORM\OneToOne(targetEntity="\Customize\Entity\MstProduct")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="product_cd", referencedColumnName="product_code")
     * })
     */
    private $MstProduct;

    /**
     * Set MstProduct.
     *
     * @param \Customize\Entity\MstProduct|null $mst_product
     *
     * @return Product
     */
    public function setMstProduct(\Customize\Entity\MstProduct $mst_product = null)
    {
        $this->MstProduct = $mst_product;

        return $this;
    }

    /**
     * Get MstProduct.
     *
     * @return \Customize\Entity\MstProduct|null
     */
    public function getMstProduct()
    {
        return $this->MstProduct;
    }

//    /**
//     * @var string
//     *
//     * @ORM\Column(name="product_code", type="string", length=20)
//     */
//    private $product_code;
//
//    /**
//     * @return string
//     */
//    public function getProductCode(): string
//    {
//        return $this->product_code;
//    }
//
//    /**
//     * @param string $product_code
//     */
//    public function setProductCode(string $product_code): void
//    {
//        $this->product_code = $product_code;
//    }
}

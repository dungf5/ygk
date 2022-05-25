<?php


namespace Customize\Entity;

use Doctrine\ORM\Mapping as ORM;
use Eccube\Annotation\EntityExtension;
use Eccube\Entity\Order;

/**
 * @EntityExtension("Eccube\Entity\Order")
 */
trait OrderTrait
{
    /**
     * @var \Eccube\Entity\Order
     */
    private $Order;
    /**
     * @var string|null
     *
     * @ORM\Column(name="seikyu_code", type="string", nullable=true,length=255)
     */
    private $seikyu_code;

    /**
     * @var string|null
     *
     * @ORM\Column(name="shipping_code", type="string", nullable=true,length=255)
     */
    private $shipping_code;
    /**
     * @var string|null
     *
     * @ORM\Column(name="otodoke_code", type="string", nullable=true,length=255)
     */
    private $otodoke_code;

    /**
     * Set DtbOrder.
     *
     * @param \Eccube\Entity\Order|null $Order
     *
     * @return Order
     */
    public function setOrder(\Eccube\Entity\Order $dtbOrder = null)
    {
        $this->Order = $dtbOrder;

        return $this;
    }

    /**
     * Get MstProduct.
     *
     * @return \Eccube\Entity\Order|null
     */
    public function getOrder()
    {
        return $this->Order;
    }
}

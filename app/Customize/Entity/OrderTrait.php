<?php


namespace Customize\Entity;

use Doctrine\ORM\Mapping as ORM;
use Eccube\Annotation\EntityExtension;
use Eccube\Entity\Order;

/**
 * @EntityExtension("Customize\Entity\Order")
 */
trait OrderTrait
{
    /**
     * @var \Customize\Entity\Order
     */
    private $Order;


    /**
     * Set DtbOrder.
     *
     * @param \Customize\Entity\Order|null $Order
     *
     * @return Order
     */
    public function setOrder(\Customize\Entity\Order $dtbOrder = null)
    {
        $this->Order = $dtbOrder;

        return $this;
    }

    /**
     * Get MstProduct.
     *
     * @return \Customize\Entity\Order|null
     */
    public function getOrder()
    {
        return $this->Order;
    }
}

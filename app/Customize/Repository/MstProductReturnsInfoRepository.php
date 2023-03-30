<?php

namespace Customize\Repository;

use Eccube\Repository\AbstractRepository;
use Customize\Entity\MstProductReturnsInfo;
use Symfony\Bridge\Doctrine\RegistryInterface;
use Doctrine\ORM\Query\Expr\Join;

class MstProductReturnsInfoRepository extends AbstractRepository
{
    /**
     * MstProductRepository constructor.
     *
     * @param RegistryInterface $registry
     */
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, MstProductReturnsInfo::class);
    }

    public function insertData($data = [])
    {
        if (empty($data)) return;

        try {
            $object = $this->findOneBy([ 'returns_no' => $data['returns_no'] ]);
            if( !$object ) {
                $object = new MstProductReturnsInfo();
                $object->setReturnsNo($data['returns_no']);
            }
            
            $object->setCustomerCode($data['customer_code']);
            $object->setShippingCode($data['shipping_code']);
            $object->setShippingName($data['shipping_name']);
            $object->setOtodokeCode($data['otodoke_code']);
            $object->setOtodokeName($data['otodoke_name']);
            $object->setShippingNo($data['shipping_no']);
            $object->setShippingDate($data['shipping_date']);
            $object->setJanCode($data['jan_code']);
            $object->setProductCode($data['product_code']);
            $object->setShippingNum($data['shipping_num']);
            $object->setReasonReturnsCode($data['reason_returns_code']);
            $object->setCustomerComment($data['customer_comment']);
            $object->setReturnsNum($data['rerurn_num']);
            $object->setCusReviewsFlag($data['cus_reviews_flag']);
            $object->setCusImageUrlPath1($data['cus_image_url_path1']);
            $object->setCusImageUrlPath2($data['cus_image_url_path2']);
            $object->setCusImageUrlPath3($data['cus_image_url_path3']);
            $object->setCusImageUrlPath4($data['cus_image_url_path4']);
            $object->setCusImageUrlPath5($data['cus_image_url_path5']);
            $object->setCusImageUrlPath6($data['cus_image_url_path6']);
            $object->setReturnsStatusFlag($data['returns_status_flag']);
            $object->setReturnsRequestDate($data['returns_request_date']);
            
            $this->getEntityManager()->persist($object);
            $this->getEntityManager()->flush();

            return $object;
        } catch (\Exception $e) {
        }

        return;
    }

    public function getReturnByCustomer($paramSearch = [], $customer_id)
    {
        $qb = $this->getEntityManager()->createQueryBuilder();
        $qb->select('product_returns_info.returns_no');
        $qb->from('Customize\Entity\MstProductReturnsInfo', 'product_returns_info');

        $qb->leftJoin(
            'Customize\Entity\MstProduct',
            'product',
            Join::WITH,
            "product.product_code = product_returns_info.product_code"
        );

        $qb->andWhere('product_returns_info.customer_code = :customer_id' )
            ->setParameter('customer_id', $customer_id);
        $qb->andWhere('product_returns_info.returns_request_date >= :returns_request_date')
            ->setParameter('returns_request_date', date("Y-m-d", strtotime("-24 MONTH")));

        $qb->addSelect(
            'product_returns_info.returns_num',
            'product_returns_info.returns_status_flag',
            'product_returns_info.shipping_no',
            'product_returns_info.shipping_date',
            'product_returns_info.shipping_name',
            'product_returns_info.otodoke_name',
            'product_returns_info.jan_code',
            'product.product_name',
            'product_returns_info.shipping_num',
        );

        if ( $paramSearch['search_jan_code'] != '' ) {
            $qb->andWhere( 'product_returns_info.jan_code LIKE :search_jan_code' )
                ->setParameter(':search_jan_code', "%{$paramSearch['search_jan_code']}%");
        }

        if ( $paramSearch['search_shipping_date'] != 0 ) {
            $qb->andWhere( 'product_returns_info.shipping_date LIKE :search_shipping_date' )
                ->setParameter(':search_shipping_date', "{$paramSearch['search_shipping_date']}-%");
        }

        if ( $paramSearch['search_shipping'] != '0' ) {
            $qb->andWhere( 'product_returns_info.shipping_code = :search_shipping' )
                ->setParameter(':search_shipping', $paramSearch['search_shipping']);
        }

        if ( $paramSearch['search_otodoke'] != '0' ) {
            $qb->andWhere( 'product_returns_info.otodoke_code = :search_otodoke' )
                ->setParameter(':search_otodoke', $paramSearch['search_otodoke']);
        }

        // //group
        $qb->addGroupBy('product_returns_info.returns_no');

        // // Order By
        $qb->addOrderBy('product_returns_info.shipping_date', 'DESC');
        $qb->addOrderBy('product_returns_info.returns_no', 'DESC');

        // dump($qb->getQuery()->getSQL());
        // dump($qb->getParameters());
        // die();
        return $qb;
    }

    public function getQueryBuilderByCustomer($param = [], $customer_id = '')
    {
        $qb = $this->getEntityManager()->createQueryBuilder();
        $qb->select('product_returns_info.returns_no');
        $qb->from('Customize\Entity\MstProductReturnsInfo', 'product_returns_info');
        $qb->leftJoin('Customize\Entity\DtReturnsReson', 'returns_reson', Join::WITH, 'returns_reson.returns_reson_id=product_returns_info.reason_returns_code');
        
        $qb->addSelect(
            'product_returns_info.returns_request_date',
            'returns_reson.returns_reson',
            'product_returns_info.shipping_no',
            'product_returns_info.shipping_date',
            'product_returns_info.shipping_name',
            'product_returns_info.otodoke_name',
            'product_returns_info.jan_code',
            'product_returns_info.product_code',
            'product_returns_info.shipping_num',
            'product_returns_info.returns_num',
        );
        
        $qb->andWhere('product_returns_info.customer_code = :customer_code')
            ->setParameter('customer_code', $customer_id);
        $qb->andWhere('product_returns_info.returns_request_date >= :returns_request_date')
            ->setParameter('returns_request_date', date("Y-m-d", strtotime("-14 MONTH")));

        if ( $param['search_request_date'] != 0 ) {
            $qb->andWhere( 'product_returns_info.returns_request_date LIKE :search_request_date' )
                ->setParameter(':search_request_date', "{$param['search_request_date']}-%");
        }

        if ( $param['search_reason_return'] != 0 ) {
            $qb->andWhere( 'product_returns_info.reason_returns_code = :search_reason_return' )
                ->setParameter(':search_reason_return', $param['search_reason_return']);
        }

        if ( $param['search_shipping'] != 0 ) {
            $qb->andWhere( 'product_returns_info.shipping_code = :search_shipping' )
                ->setParameter(':search_shipping', $param['search_shipping']);
        }

        if ( $param['search_otodoke'] != 0 ) {
            $qb->andWhere( 'product_returns_info.otodoke_code = :search_otodoke' )
                ->setParameter(':search_otodoke', $param['search_otodoke']);
        }

        // //group
        $qb->addGroupBy('product_returns_info.returns_no');

        // // Order By
        $qb->addOrderBy('product_returns_info.shipping_date', 'DESC');
        $qb->addOrderBy('product_returns_info.returns_no', 'DESC');

        // dump($qb->getQuery()->getSQL());
        // dump($qb->getParameters());
        // die();
        return $qb;
    }
}
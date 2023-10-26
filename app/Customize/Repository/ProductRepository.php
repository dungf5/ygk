<?php

/*
 * This file is part of EC-CUBE
 *
 * Copyright(c) EC-CUBE CO.,LTD. All Rights Reserved.
 *
 * http://www.ec-cube.co.jp/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Customize\Repository;

use Customize\Service\Common\MyCommonService;
use Customize\Service\GlobalService;
use Doctrine\ORM\Query\Expr\Join;
use Eccube\Common\EccubeConfig;
use Eccube\Doctrine\Query\Queries;
use Eccube\Entity\Product;
use Eccube\Repository\AbstractRepository;
use Eccube\Repository\QueryKey;
use Eccube\Util\StringUtil;
use Symfony\Bridge\Doctrine\RegistryInterface;

class ProductRepository extends AbstractRepository
{
    /**
     * @var Queries
     */
    protected $queries;

    /**
     * @var EccubeConfig
     */
    protected $eccubeConfig;

    /**
     * @var GlobalService
     */
    protected $globalService;

    public const COLUMNS = [
        'product_id' => 'p.id', 'name' => 'p.name', 'product_code' => 'pc.code', 'stock' => 'pc.stock', 'status' => 'p.Status', 'create_date' => 'p.create_date', 'update_date' => 'p.update_date',
    ];

    /**
     * ProductRepository constructor.
     *
     * @param RegistryInterface $registry
     * @param Queries $queries
     * @param EccubeConfig $eccubeConfig
     */
    public function __construct(
        RegistryInterface $registry,
        Queries $queries,
        EccubeConfig $eccubeConfig,
        GlobalService $globalService
    ) {
        parent::__construct($registry, Product::class);
        $this->queries = $queries;
        $this->eccubeConfig = $eccubeConfig;
        $this->globalService = $globalService;
    }

    /**
     * get query builder.
     *
     * @param  array $searchData
     *
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getQueryBuilderBySearchData($searchData, $customer_code = '', $shipping_code = '', $tanka_number = [], $location)
    {
        $commonService = new MyCommonService($this->getEntityManager());

        $col = '
            dtb_product.id,
            dtb_product.description_list,
            dtb_product.free_area,
            mst_product.product_code,
            mst_product.unit_price as mst_unit_price ,
            mst_product.product_name,
            mst_product.product_name_kana,
            mst_product.size,
            mst_product.color,
            mst_product.quantity,
            mst_product.jan_code,
            mst_product.material,
            mst_product.model,
            mst_product.quantity_box,
            stock_list.stock_num,
            dt_price.price_s01,
            dt_price.price_s01 as price,
            mst_delivery_plan.delivery_date AS dp_delivery_date,
            mst_delivery_plan.quanlity AS dp_quanlity
        ';

        $qb = $this->getEntityManager()->createQueryBuilder();
        $curentDate = date('Y-m-d');
        $curentDateTime = date('Y-m-d H:i:s');
        $additionalJoin = '';

        $qb->select($col)->from('Customize\Entity\Product', 'dtb_product')->where('dtb_product.Status = 1');

        // Relation
        $qb->innerJoin('Customize\Entity\MstProduct',
            'mst_product',
            Join::WITH, "mst_product.ec_product_id = dtb_product.id
            AND mst_product.jan_code <> '' 
            AND DATE_FORMAT(IFNULL(mst_product.discontinued_date, '9999-12-31 00:00:00'), '%Y-%m-%d %H:%i:%s') >= DATE_FORMAT('{$curentDateTime}', '%Y-%m-%d %H:%i:%s')"
        );

        $qb->innerJoin('Customize\Entity\Price',
            'dt_price',
            Join::WITH,
            "
            dt_price.product_code = mst_product.product_code 
            AND dt_price.shipping_no = :shipping_code 
            AND dt_price.customer_code = :customer_code 
            AND dt_price.price_s01 > 0 
            AND dt_price.valid_date <= '$curentDate' 
            AND dt_price.expire_date > '$curentDate' 
            AND dt_price.tanka_number in (:tanka_number)
            ")
            ->setParameter(':shipping_code', $shipping_code)
            ->setParameter(':customer_code', $customer_code)
            ->setParameter(':tanka_number', $tanka_number);

        if (!empty($location)) {
            $qb->leftJoin('Customize\Entity\StockList',
                'stock_list',
                Join::WITH,
                'stock_list.product_code = mst_product.product_code
                AND stock_list.stock_location = :stockLocation')
                ->setParameter(':stockLocation', $location);

            $qb->leftJoin('Customize\Entity\MstDeliveryPlan',
                'mst_delivery_plan',
                Join::WITH,
                'mst_delivery_plan.product_code = mst_product.product_code
                AND mst_delivery_plan.stock_location = :stockLocation
                AND mst_delivery_plan.delivery_date >= CURRENT_DATE()')
                ->setParameter(':stockLocation', $location);
        }
        // End - Relation

        // Filter
        if (!empty($searchData['category_id']) && $searchData['category_id']) {
            $Categories = $searchData['category_id'];

            if ($Categories) {
                $qb->innerJoin('dtb_product.ProductCategories', 'pct')
                    ->innerJoin('pct.Category', 'c')
                    ->andWhere($qb->expr()->in('pct.Category', ':Categories'))
                    ->setParameter('Categories', $Categories);
            }
        }

        if (isset($searchData['mode']) && $searchData['mode'] == 'searchLeft') {
            if (StringUtil::isNotBlank($searchData['s_product_name_kana'])) {
                $s_product_name_kana = $searchData['s_product_name_kana'];
                $s_product_name_kana = explode(' ', $s_product_name_kana);
                $orStatements = $qb->expr()->orX();

                foreach ($s_product_name_kana as $key => $value) {
                    $orStatements->add(
                        $qb->expr()->like('mst_product.product_name_kana', $qb->expr()->literal('%'.$value.'%'))
                    );
                }
                $qb->andWhere($orStatements);
            }

            if (StringUtil::isNotBlank($searchData['s_product_name'])) {
                $s_product_name = $searchData['s_product_name'];
                $s_product_name = explode(' ', $s_product_name);
                $orStatements = $qb->expr()->orX();

                foreach ($s_product_name as $key => $value) {
                    $orStatements->add(
                        $qb->expr()->like('mst_product.product_name', $qb->expr()->literal('%'.$value.'%'))
                    );
                }
                $qb->andWhere($orStatements);
            }

            if (StringUtil::isNotBlank($searchData['s_jan'])) {
                $s_jan = $searchData['s_jan'];
                $s_jan = explode(' ', $s_jan);
                $orStatements = $qb->expr()->orX();

                foreach ($s_jan as $key => $value) {
                    $orStatements->add(
                        $qb->expr()->like('mst_product.jan_code', $qb->expr()->literal('%'.$value.'%'))
                    );
                }
                $qb->andWhere($orStatements);
            }

            if (StringUtil::isNotBlank($searchData['s_catalog_code'])) {
                //$key = $searchData['s_catalog_code'];
                //$arCode = $commonService->getSearchCatalogCode($key);
                //$whereMore2 = 'mst_product.jan_code in(:jan_code_s_catalog_code)';
                //$qb->setParameter(':jan_code_s_catalog_code', $arCode);
                //$qb->andWhere($whereMore2);

                $s_catalog_code = $searchData['s_catalog_code'];
                $s_catalog_code = explode(' ', $s_catalog_code);
                $orStatements = $qb->expr()->orX();

                foreach ($s_catalog_code as $key => $value) {
                    $orStatements->add(
                        $qb->expr()->like('mst_product.catalog_code', $qb->expr()->literal('%'.$value.'%'))
                    );
                }
                $qb->andWhere($orStatements);
            }
        }

        if (isset($searchData['name']) && StringUtil::isNotBlank($searchData['name'])) {
            $key = $searchData['name'];
            $orStatements = $qb->expr()->orX();
            $orStatements->add($qb->expr()->like('mst_product.series_name', $qb->expr()->literal('%'.$key.'%')));
            $orStatements->add($qb->expr()->like('mst_product.product_name_abb', $qb->expr()->literal('%'.$key.'%')));
            $orStatements->add($qb->expr()->like('mst_product.product_name', $qb->expr()->literal('%'.$key.'%')));
            $orStatements->add($qb->expr()->like('mst_product.product_name_kana', $qb->expr()->literal('%'.$key.'%')));
            $orStatements->add($qb->expr()->like('mst_product.jan_code', $qb->expr()->literal('%'.$key.'%')));
            $orStatements->add($qb->expr()->like('mst_product.product_code', $qb->expr()->literal('%'.$key.'%')));
            $orStatements->add($qb->expr()->like('mst_product.catalog_code', $qb->expr()->literal('%'.$key.'%')));
            $qb->andWhere($orStatements);
        }
        // End - Filter

        // Order By
        if (!empty($searchData['orderby'])) {
            $config = $this->eccubeConfig;

            // JANコード順
            if ($searchData['orderby']->getId() == $config['eccube_product_jancd_lower']) {
                $qb->addOrderBy('mst_product.jan_code', 'asc');
            }

            //価格が低い順
            elseif ($searchData['orderby']->getId() == $config['eccube_product_order_price_lower']) {
                $qb->addOrderBy('price', 'asc');
            }

            // 価格が高い順
            elseif ($searchData['orderby']->getId() == $config['eccube_product_order_price_higher']) {
                $qb->addOrderBy('price', 'desc');
            }

            // 新着順
            elseif ($searchData['orderby']->getId() == $config['eccube_product_order_newer']) {
                $qb->orderBy('dtb_product.create_date', 'DESC');
                $qb->addOrderBy('dtb_product.id', 'DESC');
            } else {
                // 新着順 orderby = 0
                $qb->addOrderBy('mst_product.jan_code', 'asc');
            }
        }
        // End - Order By

        // Check Product Type
        if ($this->globalService->getProductType() == 2) {
            if ($this->globalService->getSpecialOrderFlg() == 1) {
                $qb->andWhere("(mst_product.special_order_flg <> '' AND mst_product.special_order_flg = 'Y')");
                $qb->addSelect("'2' AS product_type");
            } else {
                $qb->andWhere('(mst_product.product_code is null)');
                $qb->addSelect("'xxx' AS product_type");
            }
        } else {
            $qb->andWhere("(mst_product.special_order_flg <> 'Y' OR mst_product.special_order_flg is null)");
            $qb->addSelect("'1' AS product_type");
        }

        $qb->groupBy('mst_product.product_code');

        //var_dump($qb->getQuery()->getSQL());
        //var_dump($qb->getParameters());
        //var_dump($searchData);
        //die();

        return $this->queries->customize(QueryKey::PRODUCT_SEARCH, $qb, $searchData);
    }
}

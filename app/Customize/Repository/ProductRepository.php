<?php

namespace Customize\Repository;


use Customize\Entity\MstShipping;
use Customize\Service\Common\MyCommonService;
use Customize\Service\GlobalService;
use Doctrine\ORM\Query\Expr\Join;
use Eccube\Common\EccubeConfig;
use Eccube\Doctrine\Query\Queries;
use Eccube\Repository\AbstractRepository;
use Eccube\Entity\Product;
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
        'product_id' => 'p.id'
        ,'name' => 'p.name'
        ,'product_code' => 'pc.code'
        ,'stock' => 'pc.stock'
        ,'status' => 'p.Status'
        ,'create_date' => 'p.create_date'
        ,'update_date' => 'p.update_date'
    ];

    /**
     * ProductRepository constructor.
     * @param RegistryInterface $registry
     * @param Queries $queries
     * @param EccubeConfig $eccubeConfig
     */
    public function __construct(
        RegistryInterface $registry,
        Queries $queries,
        EccubeConfig $eccubeConfig,
        GlobalService $globalService
    )
    {
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
    public function getQueryBuilderBySearchDataA($searchData, $user = false, $customer_code = '')
    {
        $qb = $this->createQueryBuilder('p')
            ->andWhere('p.Status = 1');

        // category
        $categoryJoin = false;
        if (!empty($searchData['category_id']) && $searchData['category_id']) {
            $Categories = $searchData['category_id']->getSelfAndDescendants();
            if ($Categories) {
                $qb
                    ->innerJoin('p.ProductCategories', 'pct')
                    ->innerJoin('pct.Category', 'c')
                    ->andWhere($qb->expr()->in('pct.Category', ':Categories'))
                    ->setParameter('Categories', $Categories);
                $categoryJoin = true;
            }
        }

        // name
        if (isset($searchData['name']) && StringUtil::isNotBlank($searchData['name'])) {
            $keywords = preg_split('/[\s　]+/u', str_replace(['%', '_'], ['\\%', '\\_'], $searchData['name']), -1, PREG_SPLIT_NO_EMPTY);

            foreach ($keywords as $index => $keyword) {
                $key = sprintf('keyword%s', $index);
                $qb
                    ->andWhere(sprintf('NORMALIZE(p.name) LIKE NORMALIZE(:%s) OR
                        NORMALIZE(p.search_word) LIKE NORMALIZE(:%s) OR
                        EXISTS (SELECT wpc%d FROM \Eccube\Entity\ProductClass wpc%d WHERE p = wpc%d.Product AND NORMALIZE(wpc%d.code) LIKE NORMALIZE(:%s))',
                        $key, $key, $index, $index, $index, $index, $key))
                    ->setParameter($key, '%'.$keyword.'%');
            }
        }

        // Order By
        // 価格低い順
        $config = $this->eccubeConfig;

        if (!empty($searchData['orderby']) && $searchData['orderby']->getId() == $config['eccube_product_order_price_lower']) {
            //@see http://doctrine-orm.readthedocs.org/en/latest/reference/dql-doctrine-query-language.html
            $qb->addSelect('MIN(pc.price02) as HIDDEN price02_min');
            $qb->innerJoin('p.ProductClasses', 'pc');
            $qb->andWhere('pc.visible = true');
            $qb->groupBy('p.id');
            $qb->orderBy('price02_min', 'ASC');
            $qb->addOrderBy('p.id', 'DESC');
            // 価格高い順
        } elseif (!empty($searchData['orderby']) && $searchData['orderby']->getId() == $config['eccube_product_order_price_higher']) {
            $qb->addSelect('MAX(pc.price02) as HIDDEN price02_max');
            $qb->innerJoin('p.ProductClasses', 'pc');
            $qb->andWhere('pc.visible = true');
            $qb->groupBy('p.id');
            $qb->orderBy('price02_max', 'DESC');
            $qb->addOrderBy('p.id', 'DESC');
            // 新着順
        } elseif (!empty($searchData['orderby']) && $searchData['orderby']->getId() == $config['eccube_product_order_newer']) {
            // 在庫切れ商品非表示の設定が有効時対応
            // @see https://github.com/EC-CUBE/ec-cube/issues/1998
            if ($this->getEntityManager()->getFilters()->isEnabled('option_nostock_hidden') == true) {
                $qb->innerJoin('p.ProductClasses', 'pc');
                $qb->andWhere('pc.visible = true');
            }
            $qb->orderBy('p.create_date', 'DESC');
            $qb->addOrderBy('p.id', 'DESC');
        } else {
            if ($categoryJoin === false) {
                $qb
                    ->leftJoin('p.ProductCategories', 'pct')
                    ->leftJoin('pct.Category', 'c');
            }
            $qb
                ->addOrderBy('p.id', 'DESC');
        }
        if(!$user) {
            $customer_code = '';
        }

        $qb->innerJoin('Customize\Entity\MstProduct', 'mstProduct',Join::WITH,'mstProduct.ec_product_id = p.id');

        $qb->leftJoin('Customize\Entity\Price', 'price',Join::WITH,'price.product_code = mstProduct.product_code AND price.customer_code = :customer_code')
            ->setParameter(':customer_code', $customer_code);

        $listSelectMstProduct = "mstProduct.product_code,mstProduct.unit_price as mst_unit_price ,mstProduct.product_name";
        $listSelectMstProduct.=",mstProduct.quantity as mst_quantity,mstProduct.jan_code ";
        $qb->addSelect($listSelectMstProduct);
        $qb->addSelect('price.price_s01 as  price_s01');


        return $this->queries->customize(QueryKey::PRODUCT_SEARCH, $qb, $searchData);
    }

    public function getQueryBuilderBySearchDataNewCustom($searchData, $user = false, $customer_code = '', $shippingCode = '', $arProductCodeInDtPrice=[], $arTanakaNumber=[])
    {
        $defaultSortLoginorderPrice     = "
            (CASE
                WHEN price.price_s01  is null THEN mstProduct.unit_price
                ELSE price.price_s01
            END)
                AS hidden orderPrice
        ";

        $sqlColmnsP             = "p.id, p.description_list, p.free_area";
        $qb                     = $this->getEntityManager()->createQueryBuilder();
        $qb                     = $qb->andWhere('p.Status = 1');

        $qb ->select($sqlColmnsP)->from('Customize\Entity\Product', 'p');
        $qb->addSelect($defaultSortLoginorderPrice);

        // category
        $categoryJoin           = false;

        if (!empty($searchData['category_id']) && $searchData['category_id']) {
            $Categories         = $searchData['category_id'];

            if ($Categories) {
                $qb->innerJoin('p.ProductCategories', 'pct')
                    ->innerJoin('pct.Category', 'c')
                    ->andWhere($qb->expr()->in('pct.Category', ':Categories'))
                    ->setParameter('Categories', $Categories);

                $categoryJoin   = true;
            }
        }

        else {
            $qb->innerJoin('p.ProductCategories', 'pct')
                ->innerJoin('pct.Category', 'c');

            $categoryJoin       = true;
        }

        $newComs                = new MyCommonService($this->getEntityManager());

        if (isset($searchData['mode']) && $searchData['mode'] == "searchLeft") {
            if (StringUtil::isNotBlank($searchData['s_product_name_kana'])) {
                $key            = $searchData['s_product_name_kana'];

                $arCode         = $newComs->getSearchProductNameKana($key);
                $whereMore2     = 'mstProduct.jan_code in(:jan_code_name_kana)';
                $qb->setParameter(":jan_code_name_kana", $arCode);
                $qb->andWhere($whereMore2);
            }

            if (StringUtil::isNotBlank($searchData['s_product_name'])) {
                $key            = $searchData['s_product_name'];

                $arCode         = $newComs->getSearchProductName($key);
                $whereMore2     = 'mstProduct.jan_code in(:jan_code_name)';
                $qb->setParameter(":jan_code_name", $arCode);
                $qb->andWhere($whereMore2);
            }

            if (StringUtil::isNotBlank($searchData['s_jan'])) {
                $whereMulti     = "";
                $key            = $searchData['s_jan'];
                $arrK           = explode(' ',$key);
                $countKey       = count($arrK);

                foreach ($arrK as $item) {
                    $countKey--;
                    $item       = trim($item);

                    if ($item == "") {
                        continue;
                    }

                    if ($countKey > 0) {
                        $whereMulti     .= " (mstProduct.jan_code like :jan_code{$countKey}) or ";
                    }

                    else {
                        $whereMulti     .= " (mstProduct.jan_code like :jan_code{$countKey}) ";
                    }

                    $qb->setParameter(":jan_code{$countKey}",'%'. $item.'%');
                }

                $qb->andWhere($whereMulti);
            }

            if (StringUtil::isNotBlank($searchData['s_catalog_code'])) {
                $key            = $searchData['s_catalog_code'];
                $arCode         = $newComs->getSearchCatalogCode($key);
                $whereMore2     ='mstProduct.jan_code in(:jan_code_s_catalog_code)';
                $qb->setParameter(":jan_code_s_catalog_code",$arCode);
                $qb->andWhere($whereMore2);
            }
        }

        // name
        if (isset($searchData['name']) && StringUtil::isNotBlank($searchData['name'])) {
            $key                = $searchData['name'];
            $whereMore          = 'mstProduct.series_name like :product_name or mstProduct.product_name_abb like :product_name  or mstProduct.product_name like :product_name or mstProduct.jan_code like :product_name or mstProduct.product_code like :product_name';
            $whereMore          .=" or mstProduct.catalog_code like :product_name   ";

            $qb->andWhere($whereMore)->setParameter(':product_name','%'. $key.'%');
        }

        // Order By
        $config                 = $this->eccubeConfig;

        // JANコード順
        if (!empty($searchData['orderby']) && $searchData['orderby']->getId() == $config['eccube_product_jancd_lower']) {
            $qb->addOrderBy('mstProduct.jan_code', 'asc');
        }

        //価格が低い順
        elseif (!empty($searchData['orderby']) && $searchData['orderby']->getId() == $config['eccube_product_order_price_lower']) {
            if (!$user) {
                $qb->addOrderBy('mstProduct.unit_price', 'asc');
            }

            else {
                //price.price_s01
               $qb->addOrderBy('orderPrice', 'asc');
            }
        }

        // 価格が高い順
        elseif (!empty($searchData['orderby']) && $searchData['orderby']->getId() == $config['eccube_product_order_price_higher']) {
            if (!$user) {
                $qb->addOrderBy('mstProduct.unit_price', 'DESC');
            }

            else {
                //price.price_s01
                $qb->addOrderBy('orderPrice', 'desc');
            }
        }

        // 新着順
        elseif (!empty($searchData['orderby']) && $searchData['orderby']->getId() == $config['eccube_product_order_newer']) {
            if ($this->getEntityManager()->getFilters()->isEnabled('option_nostock_hidden') == true) {
                $qb->innerJoin('p.ProductClasses', 'pc');
                $qb->andWhere('pc.visible = true');
            }

            $qb->orderBy('p.create_date', 'DESC');
            $qb->addOrderBy('p.id', 'DESC');
        }

        else {
            if ($categoryJoin === false) {
                $qb
                    ->leftJoin('p.ProductCategories', 'pct')
                    ->leftJoin('pct.Category', 'c');
            }

            // 新着順 orderby=0
            if (!$user) {
                $qb->addOrderBy('mstProduct.unit_price', 'asc');
            }

            else {
                //price.price_s01
                $qb->addOrderBy('orderPrice', 'desc');
            }
        }

        if(!$user) {
            $customer_code = '';
        }

        $qb->innerJoin('Customize\Entity\MstProduct', 'mstProduct',Join::WITH,'mstProduct.ec_product_id = p.id');
        $qb->andWhere("(mstProduct.jan_code is not null)");
        $qb->andWhere("(mstProduct.jan_code <> '')");

        if (!$this->globalService->getSpecialOrderFlg()) {
            $qb->andWhere("(mstProduct.special_order_flg <> 'Y' OR mstProduct.special_order_flg is null)");
        }

        $curentDate         = date('Y-m-d');
        $stringCon          = ' price.product_code = mstProduct.product_code AND price.customer_code = :customer_code AND price.shipping_no = :shipping_code ';
        $stringCon          .= " and '$curentDate' >= price.valid_date AND '$curentDate' <= price.expire_date ";

        if (count($arProductCodeInDtPrice) > 0) {
            $stringCon      .= " and price.product_code in (:product_code) ";
        }

        if (count($arTanakaNumber) > 0) {
            $stringCon      .= " and price.tanka_number in (:tanka_number) ";
        }

        $qb->innerJoin('Customize\Entity\Price', 'price',Join::WITH, $stringCon)
            ->setParameter(':customer_code', $customer_code)
            ->setParameter(':shipping_code', $shippingCode);

        if (count($arProductCodeInDtPrice) > 0) {
            $qb->setParameter(':product_code', $arProductCodeInDtPrice);
        }

        if (count($arTanakaNumber) > 0) {
            $qb->setParameter(':tanka_number', $arTanakaNumber);
        }

        $listSelectMstProduct   = "mstProduct.product_code,mstProduct.unit_price as mst_unit_price ,mstProduct.product_name,mstProduct.size,mstProduct.color";
        $listSelectMstProduct   .=",mstProduct.quantity as mst_quantity,mstProduct.jan_code,mstProduct.material,mstProduct.model, mstProduct.quantity, mstProduct.quantity_box ";

        $qb->addSelect($listSelectMstProduct);
        $qb->addSelect('price.price_s01 as price_s01');

        $location   = $newComs->getCustomerLocation($customer_code);

        if( $location ) {
            $qb->leftJoin('Customize\Entity\StockList',
                'stock_list',
                Join::WITH,
                "stock_list.product_code = mstProduct.product_code
                AND stock_list.stock_location = :stockLocation")
                ->setParameter(':stockLocation', $location);
            $qb->addSelect('stock_list.stock_num');

            $qb->leftJoin('Customize\Entity\MstDeliveryPlan',
                'mst_delivery_plan',
                Join::WITH,
                "mst_delivery_plan.product_code = mstProduct.product_code
                AND mst_delivery_plan.stock_location = :stockLocation
                AND mst_delivery_plan.delivery_date >= CURRENT_DATE()")
                ->setParameter(':stockLocation', $location);

            $qb->addSelect('mst_delivery_plan.delivery_date AS dp_delivery_date');
            $qb->addSelect('mst_delivery_plan.quanlity AS dp_quanlity');
            $qb->groupBy('mst_delivery_plan.product_code');
            $qb->groupBy('mst_delivery_plan.stock_location');
        }

        $qb->groupBy('mstProduct.product_code');

        //dd( $qb->getQuery()->getSQL(), $customer_code, $searchData, $login_type, $location, $relationCus);
        return $this->queries->customize(QueryKey::PRODUCT_SEARCH, $qb, $searchData);
    }
}

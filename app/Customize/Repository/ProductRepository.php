<?php

namespace Customize\Repository;


use Customize\Entity\MstShipping;
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
        EccubeConfig $eccubeConfig)
    {
        parent::__construct($registry, Product::class);
        $this->queries = $queries;
        $this->eccubeConfig = $eccubeConfig;
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
        $listSelectMstProduct.=",mstProduct.quantity as mst_quantity ";
        $qb->addSelect($listSelectMstProduct);
        $qb->addSelect('price.price_s01 as  price_s01');


        return $this->queries->customize(QueryKey::PRODUCT_SEARCH, $qb, $searchData);
    }

    public function getQueryBuilderBySearchDataNewCustom($searchData, $user = false, $customer_code = '', $arProductCodeInDtPrice=[])
    {
        $defaultSortLoginorderPrice =" (CASE
                       WHEN price.price_s01  is null THEN mstProduct.unit_price
                      ELSE price.price_s01  end) AS hidden orderPrice ";
        $sqlColmnsP="p.id,p.description_list,p.free_area";
        $qb = $this->getEntityManager()->createQueryBuilder();
        $qb = $qb->andWhere('p.Status = 1');
        $qb ->select($sqlColmnsP)
            ->from('Customize\Entity\Product', 'p');
        $qb->addSelect($defaultSortLoginorderPrice);
        // category
        $categoryJoin = false;
        if (!empty($searchData['category_id']) && $searchData['category_id']) {
            $Categories = $searchData['category_id'];//->getescendant();//getSelfAndDescendants
           // var_dump($Categories);
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

            // 新着順 orderby=0
            if(!$user) {
                $qb->addOrderBy('mstProduct.unit_price', 'asc');
            }else{
                //price.price_s01
               $qb->addOrderBy('orderPrice', 'asc');

            }
            // 価格高い順
        } elseif (!empty($searchData['orderby']) && $searchData['orderby']->getId() == $config['eccube_product_order_price_higher']) {

            if(!$user) {
                $qb->addOrderBy('mstProduct.unit_price', 'DESC');
            }else{
                //price.price_s01
                $qb->addOrderBy('orderPrice', 'desc');

            }
            // 新着順 orderby=1
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
            // 新着順 orderby=0
            if(!$user) {
                $qb->addOrderBy('mstProduct.unit_price', 'asc');
            }else{
                //price.price_s01
                $qb->addOrderBy('orderPrice', 'desc');

            }
        }
       // $qb->andWhere("mstProduct.product_code = '100000-12-5-35-RD'");
        if(!$user) {
            $customer_code = '';
        }

        $qb->innerJoin('Customize\Entity\MstProduct', 'mstProduct',Join::WITH,'mstProduct.ec_product_id = p.id');
        $curentDate = date('Y-m-d');
        $stringCon='price.product_code = mstProduct.product_code AND price.customer_code = :customer_code  ';
        $stringCon .="and '$curentDate' >= price.valid_date    AND '$curentDate' <= price.expire_date  and price.product_code in(:product_code)";

        //$arProductCode=["200000-150-150-0.8-1","200000-150-200-1-22-"];

        $qb->leftJoin('Customize\Entity\Price', 'price',Join::WITH,$stringCon)
            ->setParameter(':customer_code', $customer_code)
            ->setParameter(':product_code', $arProductCodeInDtPrice);;
         // var_dump($arProductCodeInDtPrice);
        //valid_date = '2022/06/14'  AND '2022/06/14'<= expire_date and customer_code='9901'
        if($user) {
           // $curentDate = date('Y/m/d');
            //$qb->andWhere("price.valid_date = '$curentDate'  AND '$curentDate'<= price.expire_date and price.customer_code='$customer_code'");

        }
        $listSelectMstProduct = "mstProduct.product_code,mstProduct.unit_price as mst_unit_price ,mstProduct.product_name";
        $listSelectMstProduct.=",mstProduct.quantity as mst_quantity ";
        $qb->addSelect($listSelectMstProduct);
        $qb->addSelect('price.price_s01 as  price_s01');

       //var_dump($qb->getQuery()->getSQL(),$arProductCodeInDtPrice,$customer_code);die();
        return $this->queries->customize(QueryKey::PRODUCT_SEARCH, $qb, $searchData);
    }

}

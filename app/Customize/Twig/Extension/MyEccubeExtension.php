<?php
namespace Customize\Twig\Extension;
use Customize\Common\MyCommon;
use Customize\Common\MyConstant;
use Customize\Service\Common\MyCommonService;
use Doctrine\ORM\EntityManagerInterface;
use Eccube\Common\EccubeConfig;
use Eccube\Repository\ProductRepository;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class MyEccubeExtension extends AbstractExtension
{
    /**
     * @var EntityManagerInterface
     */
    protected $entityManager;


    /**
     * EccubeExtension constructor.
     *
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;

    }
    public function getFunctions()
    {
        return [
            new TwigFunction('getNext3Month', [$this, 'getNext3Month']),
            new TwigFunction('getMinDate', [$this, 'getMinDate']),
            new TwigFunction('getWebRootUrl', [$this, 'getWebRootUrl']),
            new TwigFunction('roundPrice', [$this, 'roundPrice']),
            new TwigFunction('getFromUrl', [$this, 'getFromUrl']),

        ];
    }

    public function getNext3Month(){
        //quyen tu cho la chon tu ngay mai den 1 thang
        //vi du cho ngay thu2 thi  thu 5 mac dinh (ko bao gom thu 7 cn va le)
        $newDate = date('Y-m-d', strtotime(date("Y-m-d"). ' + 1 months'));

      return $newDate;
    }
    public function getFromUrl($key){
       $valGet = isset($_REQUEST[$key])?$_REQUEST[$key]:'';
       return $valGet;


    }

    public function roundPrice($price){
        //$price = str_replace(",","", $price);

//      if(is_float($price)){
//        $price = round($price,2);
//      }
        $numAf = number_format($price,2);
        if(MyCommon::checkExistText($numAf,".00")){
            $numAf  = str_replace(".00","",$numAf);
        }

     return "￥".$numAf;
    }

    public function getMinDate(){
        $newDate = date('Y-m-d', strtotime(date("Y-m-d"). ' + 1 days'));
        return $newDate;

    }
    public function getWebRootUrl(){

        return MyConstant::MY_WEB;
    }

}

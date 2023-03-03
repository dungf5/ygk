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
            new TwigFunction('roundPriceZero', [$this, 'roundPriceZero']),
            new TwigFunction('roundPriceZeroTotal', [$this, 'roundPriceZeroTotal']),
            new TwigFunction('roundPriceZeroTotalAll', [$this, 'roundPriceZeroTotalAll']),
            new TwigFunction('roundPriceZeroTax', [$this, 'roundPriceZeroTax']),
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

    public function roundPrice($price) {
        $numAf      = number_format($price,2);

        if (MyCommon::checkExistText($numAf,".00")) {
            $numAf  = str_replace(".00","",$numAf);
        }

        return "￥".$numAf;
    }

    public function roundPriceZero($price,$classShow="standar_price"){

        $numAf = number_format($price,2);
        if(MyCommon::checkExistText($numAf,".00")){
            $numAf  = str_replace(".00","",$numAf);
        }
        if($classShow=="standar_price"){
            if($numAf == 0){
                return "<span style='color:#f00'>オープン価格</span>";
            }
        }
        return "￥".$numAf;
    }

    public function roundPriceZeroTotal($price)
    {
        $numAf      = number_format((int)$price);

        if (MyCommon::checkExistText($numAf,".00")) {
            $numAf  = str_replace(".00","", $numAf);
        }

        if ($numAf == 0) {
            return "";
        }

        return "￥" . $numAf;
    }

    public function roundPriceZeroTax($price)
    {
        $numAf      = number_format((int) $price);

        if (MyCommon::checkExistText($numAf,".00")) {
            $numAf  = str_replace(".00","", $numAf);
        }

        if ($numAf == 0) {
            return "";
        }

        return "￥" . $numAf;
    }

    public function roundPriceZeroTotalAll($price)
    {
        $numAf      = number_format($price,2);

        if (MyCommon::checkExistText($numAf,".00")) {
            $numAf  = str_replace(".00","", $numAf);
        }

        if ($numAf == 0) {
            return "";
        }

        $numAf      = str_replace(",","", $numAf);
        $roundUp    = (int)$numAf;
        $roundUp    = number_format($roundUp);

        return "￥" . $roundUp;
    }

    public function getMinDate(){
        $newDate = date('Y-m-d', strtotime(date("Y-m-d")));
        return $newDate;
    }

    public function getWebRootUrl(){

        return MyConstant::MY_WEB;
    }

}

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
        ];
    }

    public function getNext3Month(){
        $newDate = date('Y-m-d', strtotime(date("Y-m-d"). ' + 3 months'));

      return $newDate;
    }
    public function getMinDate(){

        $newDate = date("Y-m-d");
        return $newDate;
    }
    public function getWebRootUrl(){

        return MyConstant::MY_WEB;
    }

}

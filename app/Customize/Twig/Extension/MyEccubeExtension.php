<?php
namespace Customize\Twig\Extension;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class MyEccubeExtension extends AbstractExtension
{
    public function getFunctions()
    {
        return [
            new TwigFunction('getNext3Month', [$this, 'getNext3Month']),
            new TwigFunction('getMinDate', [$this, 'getMinDate']),
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
}

<?php
namespace Customize\Twig\Extension;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class MyEccubeExtension extends AbstractExtension
{
    public function getFunctions()
    {
        return [
            new TwigFunction('test', [$this, 'getTest']),
        ];
    }

    public function getTest($x, $y){
        $result = $x * $y;
        return "{$x} * {$y} = {$result}";
    }
}

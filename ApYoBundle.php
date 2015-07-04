<?php
namespace Ap\Bundle\YoBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Ap\Bundle\YoBundle\DependencyInjection\Compiler\YoHelperPass;

class ApYoBundle extends Bundle
{
    public function build(ContainerBuilder $container)
    {
        parent::build($container);

        $container->addCompilerPass(new YoHelperPass());
    }
}

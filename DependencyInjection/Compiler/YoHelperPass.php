<?php
namespace Ap\Bundle\YoBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;

class YoHelperPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        if (false === $container->hasDefinition('yo')) {
            return;
        }

        $definition = $container->getDefinition('yo');
        foreach ($container->findTaggedServiceIds('yo.helper') as $id => $attributes) {
            $definition->addMethodCall('addHelper', array(new Reference($id)));
        }
    }
}

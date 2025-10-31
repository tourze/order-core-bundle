<?php

namespace OrderCoreBundle\Service;

use Symfony\Component\Config\Loader\Loader;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;
use Symfony\Component\Routing\RouteCollection;
use Tourze\RoutingAutoLoaderBundle\Service\RoutingAutoLoaderInterface;

#[AutoconfigureTag(name: 'routing.loader')]
class AttributeControllerLoader extends Loader implements RoutingAutoLoaderInterface
{
    public function __construct()
    {
        parent::__construct();
    }

    public function load(mixed $resource, ?string $type = null): RouteCollection
    {
        return $this->autoload();
    }

    public function supports(mixed $resource, ?string $type = null): bool
    {
        return false;
    }

    public function autoload(): RouteCollection
    {
        return new RouteCollection();
        // DeliverExportProgressController已删除，发货功能已移到deliver-order-bundle
        // Test0503Controller已删除
        // TestNewDeliverOrderController已删除，发货功能已移到deliver-order-bundle
        // TestSubscribeLogController已删除
    }
}

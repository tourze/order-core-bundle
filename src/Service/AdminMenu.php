<?php

namespace OrderCoreBundle\Service;

use Knp\Menu\ItemInterface;
use OrderCoreBundle\Entity\Contract;
use OrderCoreBundle\Entity\OrderContact;
use OrderCoreBundle\Entity\OrderLog;
use OrderCoreBundle\Entity\OrderPrice;
use OrderCoreBundle\Entity\OrderProduct;
use OrderCoreBundle\Entity\PayOrder;
use Tourze\EasyAdminMenuBundle\Attribute\MenuProvider;
use Tourze\EasyAdminMenuBundle\Service\LinkGeneratorInterface;
use Tourze\EasyAdminMenuBundle\Service\MenuProviderInterface;

#[MenuProvider]
readonly class AdminMenu implements MenuProviderInterface
{
    public function __construct(private ?LinkGeneratorInterface $linkGenerator = null)
    {
    }

    public function __invoke(ItemInterface $item): void
    {
        if (null === $this->linkGenerator) {
            return;
        }

        if (null === $item->getChild('电商中心')) {
            $item->addChild('电商中心');
        }

        $eCommerceMenu = $item->getChild('电商中心');
        if (null !== $eCommerceMenu) {
            // 订单管理
            $eCommerceMenu->addChild('订单管理')
                ->setUri($this->linkGenerator->getCurdListPage(Contract::class))
                ->setAttribute('icon', 'fas fa-file-contract')
            ;

            // 订单相关数据
            $eCommerceMenu->addChild('订单联系人')
                ->setUri($this->linkGenerator->getCurdListPage(OrderContact::class))
                ->setAttribute('icon', 'fas fa-address-book')
            ;

            $eCommerceMenu->addChild('订单商品')
                ->setUri($this->linkGenerator->getCurdListPage(OrderProduct::class))
                ->setAttribute('icon', 'fas fa-box')
            ;

            $eCommerceMenu->addChild('订单价格')
                ->setUri($this->linkGenerator->getCurdListPage(OrderPrice::class))
                ->setAttribute('icon', 'fas fa-dollar-sign')
            ;

            $eCommerceMenu->addChild('订单日志')
                ->setUri($this->linkGenerator->getCurdListPage(OrderLog::class))
                ->setAttribute('icon', 'fas fa-history')
            ;

            $eCommerceMenu->addChild('支付订单')
                ->setUri($this->linkGenerator->getCurdListPage(PayOrder::class))
                ->setAttribute('icon', 'fas fa-credit-card')
            ;
        }
    }
}

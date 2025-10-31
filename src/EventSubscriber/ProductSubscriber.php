<?php

namespace OrderCoreBundle\EventSubscriber;

use Monolog\Attribute\WithMonologChannel;
use OrderCoreBundle\Event\BeforeOrderCreatedEvent;
use OrderLimitBundle\Service\LimitService;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Tourze\JsonRPC\Core\Exception\ApiException;
use Tourze\ProductLimitRuleBundle\Exception\LimitRuleTriggerException;

#[WithMonologChannel(channel: 'order_core')]
readonly class ProductSubscriber
{
    public function __construct(
        private LoggerInterface $logger,
        private ?LimitService $limitService,
    ) {
    }

    /**
     * 检查限制规则
     *
     * @throws ApiException
     */
    #[AsEventListener]
    public function onBeforeOrderCreated(BeforeOrderCreatedEvent $event): void
    {
        if (null === $this->limitService) {
            return;
        }

        foreach ($event->getContract()->getProducts() as $product) {
            try {
                $this->limitService->checkSpu($product);
                $this->limitService->checkSku($product);
            } catch (LimitRuleTriggerException $e) {
                $this->logger->warning('检查商品时发现条件不满足', [
                    'exception' => $e,
                    'product' => $product,
                    'event' => $event,
                ]);
                throw new \RuntimeException($e->getMessage(), $e->getCode(), $e);
            }

            try {
                $this->limitService->checkCategory($product);
            } catch (LimitRuleTriggerException $e) {
                $this->logger->warning('检查目录时发现条件不满足', [
                    'exception' => $e,
                    'product' => $product,
                    'event' => $event,
                ]);
                throw new \RuntimeException($e->getMessage(), $e->getCode(), $e);
            }
        }
    }
}

<?php

namespace OrderCoreBundle\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use OrderCoreBundle\Entity\Contract;
use OrderCoreBundle\Entity\OrderPrice;
use OrderCoreBundle\Entity\OrderProduct;
use Symfony\Component\DependencyInjection\Attribute\When;

/**
 * 订单价格数据填充
 * 创建测试用的订单价格数据
 */
#[When(env: 'test')]
#[When(env: 'dev')]
class OrderPriceFixtures extends Fixture implements DependentFixtureInterface, FixtureGroupInterface
{
    public const ORDER_PRICE_PHONE_PRODUCT = 'order-price-phone-product';
    public const ORDER_PRICE_LAPTOP_PRODUCT = 'order-price-laptop-product';
    public const ORDER_PRICE_FREIGHT = 'order-price-freight';
    public const ORDER_PRICE_DISCOUNT = 'order-price-discount';

    public function load(ObjectManager $manager): void
    {
        // 获取合同和产品引用
        $pendingContract = $this->getReference(ContractFixtures::CONTRACT_PENDING, Contract::class);
        $paidContract = $this->getReference(ContractFixtures::CONTRACT_PAID, Contract::class);
        $shippedContract = $this->getReference(ContractFixtures::CONTRACT_SHIPPED, Contract::class);
        $receivedContract = $this->getReference(ContractFixtures::CONTRACT_RECEIVED, Contract::class);

        $phoneProduct = $this->getReference(OrderProductFixtures::ORDER_PRODUCT_PHONE, OrderProduct::class);
        $laptopProduct = $this->getReference(OrderProductFixtures::ORDER_PRODUCT_LAPTOP, OrderProduct::class);
        $clothesProduct = $this->getReference(OrderProductFixtures::ORDER_PRODUCT_CLOTHES, OrderProduct::class);

        // 手机产品价格
        $phonePrice = new OrderPrice();
        $phonePrice->setContract($pendingContract);
        $phonePrice->setProduct($phoneProduct);
        $phonePrice->setName('iPhone商品价格');
        $phonePrice->setCurrency('CNY');
        $phonePrice->setMoney('2999.00');
        $phonePrice->setTax('299.90');
        $phonePrice->setPaid(false);
        $phonePrice->setCanRefund(true);
        $phonePrice->setRefund(false);
        $phonePrice->setRemark('iPhone 15 Pro Max商品价格');

        $manager->persist($phonePrice);

        // 笔记本产品价格
        $laptopPrice = new OrderPrice();
        $laptopPrice->setContract($paidContract);
        $laptopPrice->setProduct($laptopProduct);
        $laptopPrice->setName('MacBook商品价格');
        $laptopPrice->setCurrency('CNY');
        $laptopPrice->setMoney('8999.00');
        $laptopPrice->setTax('899.90');
        $laptopPrice->setPaid(true);
        $laptopPrice->setCanRefund(true);
        $laptopPrice->setRefund(false);
        $laptopPrice->setRemark('MacBook Pro商品价格');

        $manager->persist($laptopPrice);

        // 运费价格
        $freightPrice = new OrderPrice();
        $freightPrice->setContract($shippedContract);
        $freightPrice->setName('快递运费');
        $freightPrice->setCurrency('CNY');
        $freightPrice->setMoney('15.00');
        $freightPrice->setTax('1.50');
        $freightPrice->setPaid(true);
        $freightPrice->setCanRefund(false);
        $freightPrice->setRefund(false);
        $freightPrice->setRemark('顺丰快递运费');

        $manager->persist($freightPrice);

        // 优惠折扣（负数）
        $discountPrice = new OrderPrice();
        $discountPrice->setContract($paidContract);
        $discountPrice->setName('满减优惠');
        $discountPrice->setCurrency('CNY');
        $discountPrice->setMoney('-200.00');
        $discountPrice->setTax('0.00');
        $discountPrice->setPaid(false);
        $discountPrice->setCanRefund(false);
        $discountPrice->setRefund(false);
        $discountPrice->setRemark('满1000减200优惠券');

        $manager->persist($discountPrice);

        // 服务费
        $serviceFee = new OrderPrice();
        $serviceFee->setContract($receivedContract);
        $serviceFee->setName('服务费');
        $serviceFee->setCurrency('CNY');
        $serviceFee->setMoney('10.00');
        $serviceFee->setTax('1.00');
        $serviceFee->setPaid(true);
        $serviceFee->setCanRefund(false);
        $serviceFee->setRefund(false);
        $serviceFee->setRemark('平台服务费');

        $manager->persist($serviceFee);

        $manager->flush();

        // 添加引用供其他Fixture使用
        $this->addReference(self::ORDER_PRICE_PHONE_PRODUCT, $phonePrice);
        $this->addReference(self::ORDER_PRICE_LAPTOP_PRODUCT, $laptopPrice);
        $this->addReference(self::ORDER_PRICE_FREIGHT, $freightPrice);
        $this->addReference(self::ORDER_PRICE_DISCOUNT, $discountPrice);
    }

    public function getDependencies(): array
    {
        return [
            ContractFixtures::class,
            OrderProductFixtures::class,
        ];
    }

    public static function getGroups(): array
    {
        return ['order', 'test'];
    }
}

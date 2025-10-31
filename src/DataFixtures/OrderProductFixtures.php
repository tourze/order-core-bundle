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
use Tourze\ProductCoreBundle\Enum\PriceType;

/**
 * 订单产品数据填充
 * 创建测试用的订单产品数据
 */
#[When(env: 'test')]
#[When(env: 'dev')]
class OrderProductFixtures extends Fixture implements DependentFixtureInterface, FixtureGroupInterface
{
    public const ORDER_PRODUCT_PHONE = 'order-product-phone';
    public const ORDER_PRODUCT_LAPTOP = 'order-product-laptop';
    public const ORDER_PRODUCT_CLOTHES = 'order-product-clothes';
    public const ORDER_PRODUCT_BOOK = 'order-product-book';

    public function load(ObjectManager $manager): void
    {
        // 获取合同引用
        $pendingContract = $this->getReference(ContractFixtures::CONTRACT_PENDING, Contract::class);
        $paidContract = $this->getReference(ContractFixtures::CONTRACT_PAID, Contract::class);
        $shippedContract = $this->getReference(ContractFixtures::CONTRACT_SHIPPED, Contract::class);
        $receivedContract = $this->getReference(ContractFixtures::CONTRACT_RECEIVED, Contract::class);

        // 为待支付订单创建产品
        $phoneProduct = new OrderProduct();
        $phoneProduct->setContract($pendingContract);
        $phoneProduct->setValid(true);
        $phoneProduct->setQuantity(1);
        $phoneProduct->setRemark('iPhone 15 Pro Max 256GB');

        // 使用 OrderPrice 设置价格和货币
        $phonePrice = new OrderPrice();
        $phonePrice->setName('iPhone 15 Pro Max 价格');
        $phonePrice->setCurrency('CNY');
        $phonePrice->setMoney('9999.00');
        $phonePrice->setTax('999.00');
        $phonePrice->setType(PriceType::SALE);
        $phonePrice->setProduct($phoneProduct);
        $phonePrice->setContract($pendingContract);
        $phoneProduct->addPrice($phonePrice);

        $manager->persist($phoneProduct);
        $manager->persist($phonePrice);

        // 为已支付订单创建产品
        $laptopProduct = new OrderProduct();
        $laptopProduct->setContract($paidContract);
        $laptopProduct->setValid(true);
        $laptopProduct->setQuantity(1);
        $laptopProduct->setRemark('MacBook Pro 14inch M3');

        // 使用 OrderPrice 设置价格和货币
        $laptopPrice = new OrderPrice();
        $laptopPrice->setName('MacBook Pro 价格');
        $laptopPrice->setCurrency('CNY');
        $laptopPrice->setMoney('15999.00');
        $laptopPrice->setTax('1599.00');
        $laptopPrice->setType(PriceType::SALE);
        $laptopPrice->setProduct($laptopProduct);
        $laptopPrice->setContract($paidContract);
        $laptopProduct->addPrice($laptopPrice);

        $manager->persist($laptopProduct);
        $manager->persist($laptopPrice);

        // 为已发货订单创建产品
        $clothesProduct = new OrderProduct();
        $clothesProduct->setContract($shippedContract);
        $clothesProduct->setValid(true);
        $clothesProduct->setQuantity(2);
        $clothesProduct->setRemark('夏季T恤 L码');

        // 使用 OrderPrice 设置价格和货币
        $clothesPrice = new OrderPrice();
        $clothesPrice->setName('T恤价格');
        $clothesPrice->setCurrency('CNY');
        $clothesPrice->setMoney('99.00');
        $clothesPrice->setTax('9.90');
        $clothesPrice->setType(PriceType::SALE);
        $clothesPrice->setProduct($clothesProduct);
        $clothesPrice->setContract($shippedContract);
        $clothesProduct->addPrice($clothesPrice);

        $manager->persist($clothesProduct);
        $manager->persist($clothesPrice);

        // 为已完成订单创建产品
        $bookProduct = new OrderProduct();
        $bookProduct->setContract($receivedContract);
        $bookProduct->setValid(true);
        $bookProduct->setQuantity(3);
        $bookProduct->setRemark('PHP编程指南');

        // 使用 OrderPrice 设置价格和货币
        $bookPrice = new OrderPrice();
        $bookPrice->setName('图书价格');
        $bookPrice->setCurrency('CNY');
        $bookPrice->setMoney('89.00');
        $bookPrice->setTax('8.90');
        $bookPrice->setType(PriceType::SALE);
        $bookPrice->setProduct($bookProduct);
        $bookPrice->setContract($receivedContract);
        $bookProduct->addPrice($bookPrice);

        $manager->persist($bookProduct);
        $manager->persist($bookPrice);

        // 为已支付订单添加第二个产品
        $accessoryProduct = new OrderProduct();
        $accessoryProduct->setContract($paidContract);
        $accessoryProduct->setValid(true);
        $accessoryProduct->setQuantity(1);
        $accessoryProduct->setRemark('蓝牙无线鼠标');

        // 使用 OrderPrice 设置价格和货币
        $accessoryPrice = new OrderPrice();
        $accessoryPrice->setName('鼠标价格');
        $accessoryPrice->setCurrency('CNY');
        $accessoryPrice->setMoney('199.00');
        $accessoryPrice->setTax('19.90');
        $accessoryPrice->setType(PriceType::SALE);
        $accessoryPrice->setProduct($accessoryProduct);
        $accessoryPrice->setContract($paidContract);
        $accessoryProduct->addPrice($accessoryPrice);

        $manager->persist($accessoryProduct);
        $manager->persist($accessoryPrice);

        $manager->flush();

        // 添加引用供其他Fixture使用
        $this->addReference(self::ORDER_PRODUCT_PHONE, $phoneProduct);
        $this->addReference(self::ORDER_PRODUCT_LAPTOP, $laptopProduct);
        $this->addReference(self::ORDER_PRODUCT_CLOTHES, $clothesProduct);
        $this->addReference(self::ORDER_PRODUCT_BOOK, $bookProduct);
    }

    public function getDependencies(): array
    {
        return [
            ContractFixtures::class,
        ];
    }

    public static function getGroups(): array
    {
        return ['order', 'test'];
    }
}

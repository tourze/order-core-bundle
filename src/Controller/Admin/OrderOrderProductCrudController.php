<?php

namespace OrderCoreBundle\Controller\Admin;

use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminCrud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\MoneyField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Filter\DateTimeFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\EntityFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\NumericFilter;
use OrderCoreBundle\Entity\OrderProduct;

#[AdminCrud(routePath: '/order/order-product', routeName: 'order_order_product')]
final class OrderOrderProductCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return OrderProduct::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('订单商品')
            ->setEntityLabelInPlural('订单商品列表')
            ->setPageTitle('index', '订单商品管理')
            ->setPageTitle('detail', '订单商品详情')
            ->setPageTitle('edit', '编辑订单商品')
            ->setPageTitle('new', '创建订单商品')
            ->setHelp('index', '管理所有订单商品信息')
            ->setDefaultSort(['id' => 'DESC'])
            ->setSearchFields(['id'])
        ;
    }

    public function configureFields(string $pageName): iterable
    {
        yield IdField::new('id', 'ID')->setMaxLength(9999);
        yield AssociationField::new('contract', '订单');
        yield AssociationField::new('spu', 'SPU');
        yield AssociationField::new('sku', 'SKU');
        yield IntegerField::new('quantity', '数量');
        yield MoneyField::new('price', '售价')->setCurrency('CNY');
        yield TextField::new('currency', '币种');
        yield TextField::new('remark', '备注')->onlyOnDetail();
        yield DateTimeField::new('createTime', '创建时间')
            ->hideOnForm()
            ->setFormat('yyyy-MM-dd HH:mm:ss')
        ;
        yield DateTimeField::new('updateTime', '更新时间')
            ->hideOnForm()
            ->setFormat('yyyy-MM-dd HH:mm:ss')
        ;
    }

    public function configureActions(Actions $actions): Actions
    {
        return $actions
            ->add(Crud::PAGE_INDEX, Action::DETAIL)
        ;
    }

    public function configureFilters(Filters $filters): Filters
    {
        return $filters
            ->add(EntityFilter::new('contract', '订单'))
            ->add(EntityFilter::new('spu', 'SPU'))
            ->add(EntityFilter::new('sku', 'SKU'))
            ->add(NumericFilter::new('quantity', '数量'))
            ->add(NumericFilter::new('price', '售价'))
            ->add(DateTimeFilter::new('createTime', '创建时间'))
        ;
    }
}

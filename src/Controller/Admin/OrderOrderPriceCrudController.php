<?php

namespace OrderCoreBundle\Controller\Admin;

use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminCrud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\MoneyField;
use EasyCorp\Bundle\EasyAdminBundle\Field\NumberField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Filter\BooleanFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\ChoiceFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\DateTimeFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\EntityFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\TextFilter;
use OrderCoreBundle\Entity\OrderPrice;
use Tourze\ProductCoreBundle\Enum\PriceType;

#[AdminCrud(routePath: '/order/price', routeName: 'order_price')]
final class OrderOrderPriceCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return OrderPrice::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('订单价格')
            ->setEntityLabelInPlural('订单价格列表')
            ->setPageTitle('index', '订单价格管理')
            ->setPageTitle('detail', '订单价格详情')
            ->setPageTitle('edit', '编辑订单价格')
            ->setPageTitle('new', '创建订单价格')
            ->setHelp('index', '管理订单的价格明细信息')
            ->setDefaultSort(['id' => 'DESC'])
            ->setSearchFields(['name', 'remark'])
        ;
    }

    public function configureActions(Actions $actions): Actions
    {
        return $actions
            ->add(Crud::PAGE_INDEX, Action::DETAIL)
        ;
    }

    public function configureFields(string $pageName): iterable
    {
        yield IdField::new('id', 'ID')->setMaxLength(9999);
        yield AssociationField::new('contract', '订单合同');
        yield AssociationField::new('product', '关联商品');
        yield TextField::new('name', '价格名目');
        yield ChoiceField::new('type', '价格类型')
            ->setChoices([
                '销售价' => PriceType::SALE,
                '成本价' => PriceType::COST,
                '竞价' => PriceType::COMPETE,
                '运费' => PriceType::FREIGHT,
                '营销费用' => PriceType::MARKETING,
                '原价' => PriceType::ORIGINAL_PRICE,
            ])
        ;
        yield MoneyField::new('money', '金额')->setCurrency('CNY');
        yield NumberField::new('unitPrice', '单价')->setNumDecimals(2);
        yield MoneyField::new('tax', '税费')->setCurrency('CNY');
        yield TextField::new('currency', '币种')->setHelp('默认为CNY');
        yield BooleanField::new('paid', '是否已支付');
        yield BooleanField::new('canRefund', '是否可退款');
        yield BooleanField::new('refund', '是否已退款');
        yield TextareaField::new('remark', '备注');
        yield AssociationField::new('skuPrice', 'SKU价格')->hideOnForm();
        yield DateTimeField::new('createTime', '创建时间')
            ->hideOnForm()
            ->setFormat('yyyy-MM-dd HH:mm:ss')
        ;
        yield DateTimeField::new('updateTime', '更新时间')
            ->hideOnForm()
            ->setFormat('yyyy-MM-dd HH:mm:ss')
        ;
        yield TextField::new('createdBy', '创建人')->hideOnForm();
        yield TextField::new('updatedBy', '更新人')->hideOnForm();
    }

    public function configureFilters(Filters $filters): Filters
    {
        return $filters
            ->add(EntityFilter::new('contract', '订单合同'))
            ->add(EntityFilter::new('product', '关联商品'))
            ->add(TextFilter::new('name', '价格名目'))
            ->add(ChoiceFilter::new('type', '价格类型')
                ->setChoices([
                    '销售价' => PriceType::SALE,
                    '成本价' => PriceType::COST,
                    '竞价' => PriceType::COMPETE,
                    '运费' => PriceType::FREIGHT,
                    '营销费用' => PriceType::MARKETING,
                    '原价' => PriceType::ORIGINAL_PRICE,
                ]))
            ->add(BooleanFilter::new('paid', '是否已支付'))
            ->add(BooleanFilter::new('canRefund', '是否可退款'))
            ->add(BooleanFilter::new('refund', '是否已退款'))
            ->add(DateTimeFilter::new('createTime', '创建时间'))
        ;
    }
}

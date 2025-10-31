<?php

namespace OrderCoreBundle\Controller\Admin;

use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminCrud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Filter\ChoiceFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\DateTimeFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\EntityFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\TextFilter;
use OrderCoreBundle\Entity\Contract;
use OrderCoreBundle\Enum\OrderState;
use Symfony\Component\Form\Extension\Core\Type\EnumType;

#[AdminCrud(routePath: '/order/contract', routeName: 'order_contract')]
final class OrderContractCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Contract::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('订单')
            ->setEntityLabelInPlural('订单列表')
            ->setPageTitle('index', '订单管理')
            ->setPageTitle('detail', '订单详情')
            ->setPageTitle('edit', '编辑订单')
            ->setPageTitle('new', '创建订单')
            ->setHelp('index', '管理所有订单信息')
            ->setDefaultSort(['id' => 'DESC'])
            ->setSearchFields(['id', 'sn', 'type'])
        ;
    }

    public function configureFields(string $pageName): iterable
    {
        yield IdField::new('id', 'ID')->setMaxLength(9999);
        yield TextField::new('sn', '订单编号');
        yield TextField::new('type', '订单类型');
        yield AssociationField::new('user', '用户');
        yield ChoiceField::new('state', '状态')
            ->setFormType(EnumType::class)
            ->setFormTypeOptions(['class' => OrderState::class])
            ->formatValue(function ($value) {
                return $value instanceof OrderState ? $value->getLabel() : '';
            })
        ;
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
        $stateChoices = [];
        foreach (OrderState::cases() as $case) {
            $stateChoices[$case->getLabel()] = $case->value;
        }

        return $filters
            ->add(TextFilter::new('sn', '订单编号'))
            ->add(TextFilter::new('type', '订单类型'))
            ->add(ChoiceFilter::new('state', '订单状态')->setChoices($stateChoices))
            ->add(EntityFilter::new('user', '用户'))
            ->add(DateTimeFilter::new('createTime', '创建时间'))
        ;
    }
}

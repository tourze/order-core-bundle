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
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Filter\BooleanFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\ChoiceFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\DateTimeFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\EntityFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\TextFilter;
use OrderCoreBundle\Entity\OrderContact;
use OrderCoreBundle\Enum\CardType;
use Symfony\Component\Form\Extension\Core\Type\EnumType;

#[AdminCrud(routePath: '/order/order-contact', routeName: 'order_order_contact')]
final class OrderOrderContactCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return OrderContact::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('订单联系人')
            ->setEntityLabelInPlural('订单联系人列表')
            ->setPageTitle('index', '订单联系人管理')
            ->setPageTitle('detail', '订单联系人详情')
            ->setPageTitle('edit', '编辑订单联系人')
            ->setPageTitle('new', '创建订单联系人')
            ->setHelp('index', '管理所有订单联系人信息')
            ->setDefaultSort(['id' => 'DESC'])
            ->setSearchFields(['id', 'realname', 'mobile', 'email'])
        ;
    }

    public function configureFields(string $pageName): iterable
    {
        yield IdField::new('id', 'ID')->setMaxLength(9999)->onlyOnIndex();
        yield AssociationField::new('contract', '订单');
        yield TextField::new('realname', '姓名');
        yield TextField::new('mobile', '手机号');
        yield ChoiceField::new('cardType', '证件类型')
            ->setFormType(EnumType::class)
            ->setFormTypeOptions(['class' => CardType::class])
            ->formatValue(function ($value) {
                return $value instanceof CardType ? $value->getLabel() : '';
            })
        ;
        yield TextField::new('idCard', '证件号')->hideOnIndex();
        yield TextField::new('address', '地址')->hideOnIndex();
        yield TextField::new('email', '邮箱')->hideOnIndex();
        yield TextField::new('provinceName', '省份')->hideOnIndex();
        yield TextField::new('cityName', '城市')->hideOnIndex();
        yield TextField::new('areaName', '地区')->hideOnIndex();
        yield TextField::new('name', '姓名（别名）')->hideOnIndex();
        yield TextField::new('phone', '电话（别名）')->hideOnIndex();
        yield TextField::new('position', '职位')->hideOnIndex();
        yield TextField::new('department', '部门')->hideOnIndex();
        yield TextField::new('contactType', '联系人类型')->hideOnIndex();
        yield BooleanField::new('isActive', '是否激活');
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
        $cardTypeChoices = [];
        foreach (CardType::cases() as $case) {
            $cardTypeChoices[$case->getLabel()] = $case->value;
        }

        return $filters
            ->add(EntityFilter::new('contract', '订单'))
            ->add(TextFilter::new('realname', '姓名'))
            ->add(TextFilter::new('mobile', '手机号'))
            ->add(ChoiceFilter::new('cardType', '证件类型')->setChoices($cardTypeChoices))
            ->add(TextFilter::new('email', '邮箱'))
            ->add(TextFilter::new('position', '职位'))
            ->add(BooleanFilter::new('isActive', '是否激活'))
            ->add(DateTimeFilter::new('createTime', '创建时间'))
        ;
    }
}

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
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Filter\ChoiceFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\DateTimeFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\EntityFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\TextFilter;
use OrderCoreBundle\Entity\OrderLog;
use OrderCoreBundle\Enum\OrderState;

#[AdminCrud(routePath: '/order/log', routeName: 'order_log')]
final class OrderOrderLogCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return OrderLog::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('订单日志')
            ->setEntityLabelInPlural('订单日志列表')
            ->setPageTitle('index', '订单日志管理')
            ->setPageTitle('detail', '订单日志详情')
            ->setHelp('index', '查看所有订单操作轨迹记录')
            ->setDefaultSort(['id' => 'DESC'])
            ->setSearchFields(['orderSn', 'action', 'description'])
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
        yield ChoiceField::new('currentState', '当前状态')
            ->setChoices([
                '初始' => OrderState::INIT,
                '已取消' => OrderState::CANCELED,
                '支付中' => OrderState::PAYING,
                '已支付' => OrderState::PAID,
                '部分发货' => OrderState::PART_SHIPPED,
                '已发货' => OrderState::SHIPPED,
                '已收货' => OrderState::RECEIVED,
                '已过期' => OrderState::EXPIRED,
                '售后中' => OrderState::AFTERSALES_ING,
                '售后成功' => OrderState::AFTERSALES_SUCCESS,
                '售后失败' => OrderState::AFTERSALES_FAILED,
                '审核中' => OrderState::AUDITING,
                '已接受' => OrderState::ACCEPT_ORDER,
                '已拒绝' => OrderState::REJECT_ORDER,
                '异常' => OrderState::EXCEPTION,
            ])
        ;
        yield TextField::new('orderSn', '订单号');
        yield TextField::new('action', '操作动作');
        yield TextareaField::new('description', '描述信息');
        yield DateTimeField::new('createTime', '创建时间')
            ->hideOnForm()
            ->setFormat('yyyy-MM-dd HH:mm:ss')
        ;
        yield TextField::new('createdBy', '创建人')->hideOnForm();
        yield TextField::new('createdFromIp', '创建IP')->hideOnForm();
    }

    public function configureFilters(Filters $filters): Filters
    {
        return $filters
            ->add(EntityFilter::new('contract', '订单合同'))
            ->add(ChoiceFilter::new('currentState', '当前状态')
                ->setChoices([
                    '初始' => OrderState::INIT,
                    '已取消' => OrderState::CANCELED,
                    '支付中' => OrderState::PAYING,
                    '已支付' => OrderState::PAID,
                    '部分发货' => OrderState::PART_SHIPPED,
                    '已发货' => OrderState::SHIPPED,
                    '已收货' => OrderState::RECEIVED,
                    '已过期' => OrderState::EXPIRED,
                    '售后中' => OrderState::AFTERSALES_ING,
                    '售后成功' => OrderState::AFTERSALES_SUCCESS,
                    '售后失败' => OrderState::AFTERSALES_FAILED,
                    '审核中' => OrderState::AUDITING,
                    '已接受' => OrderState::ACCEPT_ORDER,
                    '已拒绝' => OrderState::REJECT_ORDER,
                    '异常' => OrderState::EXCEPTION,
                ]))
            ->add(TextFilter::new('orderSn', '订单号'))
            ->add(TextFilter::new('action', '操作动作'))
            ->add(DateTimeFilter::new('createTime', '创建时间'))
        ;
    }
}

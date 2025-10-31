<?php

declare(strict_types=1);

namespace PrepaidCardBundle\Controller\Admin;

use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminCrud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\MoneyField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Filter\DateTimeFilter;
use PrepaidCardBundle\Entity\Consumption;

#[AdminCrud(
    routePath: '/prepaid-card/consumption',
    routeName: 'prepaid_card_consumption'
)]
final class ConsumptionCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Consumption::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('消费记录')
            ->setEntityLabelInPlural('消费记录管理')
            ->setPageTitle(Crud::PAGE_INDEX, '消费记录列表')
            ->setPageTitle(Crud::PAGE_NEW, '新建消费记录')
            ->setPageTitle(Crud::PAGE_EDIT, '编辑消费记录')
            ->setPageTitle(Crud::PAGE_DETAIL, '消费记录详情')
            ->setDefaultSort(['createTime' => 'DESC'])
            ->setSearchFields(['title', 'orderId', 'card.cardNumber'])
            ->showEntityActionsInlined()
            ->setFormThemes(['@EasyAdmin/crud/form_theme.html.twig'])
        ;
    }

    public function configureFields(string $pageName): iterable
    {
        yield IdField::new('id', 'ID')
            ->onlyOnIndex()
        ;

        yield TextField::new('title', '标题')
            ->setColumns('col-md-6')
            ->setRequired(true)
            ->setMaxLength(100)
            ->setHelp('消费记录的标题描述')
        ;

        yield AssociationField::new('card', '礼品卡')
            ->setColumns('col-md-6')
            ->setRequired(true)
            ->setHelp('关联的礼品卡')
        ;

        yield TextField::new('orderId', '关联订单ID')
            ->setColumns('col-md-6')
            ->setRequired(false)
            ->setMaxLength(40)
            ->setHelp('关联的订单编号')
        ;

        yield MoneyField::new('amount', '消费金额')
            ->setColumns('col-md-6')
            ->setCurrency('CNY')
            ->setStoredAsCents(false)
            ->setRequired(true)
            ->setHelp('消费的金额，单位为元')
        ;

        yield AssociationField::new('contract', '关联订单')
            ->setColumns('col-md-6')
            ->setRequired(true)
            ->setHelp('关联的预付订单')
        ;

        yield MoneyField::new('refundableAmount', '可退款金额')
            ->setColumns('col-md-6')
            ->setCurrency('CNY')
            ->setStoredAsCents(false)
            ->setRequired(false)
            ->setHelp('可退款的金额，单位为元')
        ;

        yield DateTimeField::new('createTime', '创建时间')
            ->hideOnForm()
            ->setFormat('y-MM-dd HH:mm:ss')
        ;

        yield AssociationField::new('createdBy', '创建者')
            ->onlyOnDetail()
        ;

        yield TextField::new('createdFromIp', '创建IP')
            ->onlyOnDetail()
            ->setHelp('消费记录创建时的客户端IP地址')
        ;
    }

    public function configureFilters(Filters $filters): Filters
    {
        return $filters
            ->add('card')
            ->add('contract')
            ->add('title')
            ->add('orderId')
            ->add('amount')
            ->add('refundableAmount')
            ->add(DateTimeFilter::new('createTime'))
            ->add('createdBy')
        ;
    }
}

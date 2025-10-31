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
use PrepaidCardBundle\Entity\Contract;

#[AdminCrud(
    routePath: '/prepaid-card/contract',
    routeName: 'prepaid_card_contract'
)]
final class ContractCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Contract::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('预付订单')
            ->setEntityLabelInPlural('预付订单管理')
            ->setPageTitle(Crud::PAGE_INDEX, '订单列表')
            ->setPageTitle(Crud::PAGE_NEW, '新建订单')
            ->setPageTitle(Crud::PAGE_EDIT, '编辑订单')
            ->setPageTitle(Crud::PAGE_DETAIL, '订单详情')
            ->setDefaultSort(['createTime' => 'DESC'])
            ->setSearchFields(['code'])
            ->showEntityActionsInlined()
            ->setFormThemes(['@EasyAdmin/crud/form_theme.html.twig'])
        ;
    }

    public function configureFields(string $pageName): iterable
    {
        yield IdField::new('id', 'ID')
            ->onlyOnIndex()
        ;

        yield TextField::new('code', '订单编码')
            ->setColumns('col-md-6')
            ->setMaxLength(100)
            ->setHelp('订单的唯一标识码')
        ;

        yield MoneyField::new('costAmount', '总费用')
            ->setColumns('col-md-6')
            ->setCurrency('CNY')
            ->setStoredAsCents(false)
            ->setRequired(true)
            ->setHelp('订单的总金额，单位为元')
        ;

        yield DateTimeField::new('refundTime', '退款时间')
            ->setColumns('col-md-6')
            ->setFormat('y-MM-dd HH:mm:ss')
            ->setRequired(false)
            ->setHelp('订单退款的时间，为空表示未退款')
        ;

        yield AssociationField::new('consumptions', '消费记录')
            ->onlyOnDetail()
            ->setHelp('此订单关联的所有消费记录')
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
            ->setHelp('订单创建时的客户端IP地址')
        ;
    }

    public function configureFilters(Filters $filters): Filters
    {
        return $filters
            ->add('code')
            ->add('costAmount')
            ->add(DateTimeFilter::new('refundTime'))
            ->add(DateTimeFilter::new('createTime'))
            ->add('createdBy')
        ;
    }
}

<?php

declare(strict_types=1);

namespace PrepaidCardBundle\Controller\Admin;

use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminCrud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\ArrayField;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Filter\BooleanFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\DateTimeFilter;
use PrepaidCardBundle\Entity\Campaign;

#[AdminCrud(
    routePath: '/prepaid-card/campaign',
    routeName: 'prepaid_card_campaign'
)]
final class CampaignCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Campaign::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('礼品卡活动')
            ->setEntityLabelInPlural('礼品卡活动管理')
            ->setPageTitle(Crud::PAGE_INDEX, '活动列表')
            ->setPageTitle(Crud::PAGE_NEW, '新建活动')
            ->setPageTitle(Crud::PAGE_EDIT, '编辑活动')
            ->setPageTitle(Crud::PAGE_DETAIL, '活动详情')
            ->setDefaultSort(['createTime' => 'DESC'])
            ->setSearchFields(['title', 'company.title'])
            ->showEntityActionsInlined()
            ->setFormThemes(['@EasyAdmin/crud/form_theme.html.twig'])
        ;
    }

    public function configureFields(string $pageName): iterable
    {
        yield IdField::new('id', 'ID')
            ->onlyOnIndex()
        ;

        yield TextField::new('title', '活动名称')
            ->setColumns('col-md-6')
            ->setRequired(true)
            ->setMaxLength(100)
        ;

        yield AssociationField::new('company', '所属公司')
            ->setColumns('col-md-6')
            ->setRequired(false)
        ;

        yield DateTimeField::new('startTime', '开始时间')
            ->setColumns('col-md-6')
            ->setFormat('y-MM-dd HH:mm:ss')
        ;

        yield DateTimeField::new('expireTime', '结束时间')
            ->setColumns('col-md-6')
            ->setFormat('y-MM-dd HH:mm:ss')
        ;

        yield BooleanField::new('valid', '有效状态')
            ->renderAsSwitch(false)
        ;

        yield ArrayField::new('giveCouponIds', '赠送优惠券ID')
            ->onlyOnDetail()
        ;

        yield AssociationField::new('packages', '关联套餐')
            ->onlyOnDetail()
        ;

        yield AssociationField::new('cards', '关联卡片')
            ->onlyOnDetail()
        ;

        yield DateTimeField::new('createTime', '创建时间')
            ->hideOnForm()
            ->setFormat('y-MM-dd HH:mm:ss')
        ;

        yield DateTimeField::new('updateTime', '更新时间')
            ->onlyOnDetail()
            ->setFormat('y-MM-dd HH:mm:ss')
        ;

        yield AssociationField::new('createdBy', '创建者')
            ->onlyOnDetail()
        ;

        yield AssociationField::new('updatedBy', '更新者')
            ->onlyOnDetail()
        ;
    }

    public function configureFilters(Filters $filters): Filters
    {
        return $filters
            ->add('company')
            ->add('title')
            ->add(BooleanFilter::new('valid'))
            ->add(DateTimeFilter::new('startTime'))
            ->add(DateTimeFilter::new('expireTime'))
            ->add(DateTimeFilter::new('createTime'))
        ;
    }
}

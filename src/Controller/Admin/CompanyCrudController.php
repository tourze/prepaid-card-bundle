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
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Filter\DateTimeFilter;
use PrepaidCardBundle\Entity\Company;

#[AdminCrud(
    routePath: '/prepaid-card/company',
    routeName: 'prepaid_card_company'
)]
final class CompanyCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Company::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('预付卡公司')
            ->setEntityLabelInPlural('预付卡公司管理')
            ->setPageTitle(Crud::PAGE_INDEX, '公司列表')
            ->setPageTitle(Crud::PAGE_NEW, '新建公司')
            ->setPageTitle(Crud::PAGE_EDIT, '编辑公司')
            ->setPageTitle(Crud::PAGE_DETAIL, '公司详情')
            ->setDefaultSort(['createTime' => 'DESC'])
            ->setSearchFields(['title'])
            ->showEntityActionsInlined()
            ->setFormThemes(['@EasyAdmin/crud/form_theme.html.twig'])
        ;
    }

    public function configureFields(string $pageName): iterable
    {
        yield IdField::new('id', 'ID')
            ->onlyOnIndex()
        ;

        yield TextField::new('title', '公司名称')
            ->setColumns('col-md-6')
            ->setRequired(true)
            ->setMaxLength(255)
            ->setHelp('公司名称必须唯一')
        ;

        yield AssociationField::new('campaigns', '关联活动')
            ->onlyOnDetail()
            ->setHelp('该公司创建的所有活动')
        ;

        yield AssociationField::new('cards', '关联卡片')
            ->onlyOnDetail()
            ->setHelp('该公司发行的所有预付卡')
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
            ->add('title')
            ->add(DateTimeFilter::new('createTime'))
            ->add(DateTimeFilter::new('updateTime'))
        ;
    }
}

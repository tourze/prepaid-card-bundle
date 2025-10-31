<?php

declare(strict_types=1);

namespace PrepaidCardBundle\Controller\Admin;

use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminCrud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\MoneyField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Filter\BooleanFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\ChoiceFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\DateTimeFilter;
use PrepaidCardBundle\Entity\Card;
use PrepaidCardBundle\Enum\PrepaidCardStatus;
use Tourze\EasyAdminEnumFieldBundle\Field\EnumField;

#[AdminCrud(
    routePath: '/prepaid-card/card',
    routeName: 'prepaid_card_card'
)]
final class CardCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Card::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('礼品卡')
            ->setEntityLabelInPlural('礼品卡管理')
            ->setPageTitle(Crud::PAGE_INDEX, '礼品卡列表')
            ->setPageTitle(Crud::PAGE_NEW, '新建礼品卡')
            ->setPageTitle(Crud::PAGE_EDIT, '编辑礼品卡')
            ->setPageTitle(Crud::PAGE_DETAIL, '礼品卡详情')
            ->setDefaultSort(['createTime' => 'DESC'])
            ->setSearchFields(['cardNumber', 'company.title', 'owner.username'])
            ->showEntityActionsInlined()
            ->setFormThemes(['@EasyAdmin/crud/form_theme.html.twig'])
        ;
    }

    public function configureFields(string $pageName): iterable
    {
        yield IdField::new('id', 'ID')
            ->onlyOnIndex()
        ;

        yield TextField::new('cardNumber', '卡号')
            ->setColumns('col-md-6')
            ->setRequired(true)
            ->setMaxLength(40)
        ;

        yield TextField::new('cardPassword', '卡密')
            ->setColumns('col-md-6')
            ->setMaxLength(64)
        ;

        yield AssociationField::new('company', '所属公司')
            ->setColumns('col-md-6')
        ;

        yield MoneyField::new('parValue', '面值')
            ->setColumns('col-md-6')
            ->setCurrency('CNY')
            ->setStoredAsCents(false)
        ;

        yield MoneyField::new('balance', '余额')
            ->setColumns('col-md-6')
            ->setCurrency('CNY')
            ->setStoredAsCents(false)
        ;

        yield DateTimeField::new('bindTime', '绑定时间')
            ->setColumns('col-md-6')
            ->setFormat('y-MM-dd HH:mm:ss')
        ;

        yield DateTimeField::new('expireTime', '过期时间')
            ->setColumns('col-md-6')
            ->setFormat('y-MM-dd HH:mm:ss')
        ;

        yield AssociationField::new('owner', '卡片持有者')
            ->setColumns('col-md-6')
        ;

        $statusField = EnumField::new('status', '状态');
        $statusField->setEnumCases(PrepaidCardStatus::cases());
        yield $statusField
            ->setColumns('col-md-6')
        ;

        yield AssociationField::new('campaign', '所属活动')
            ->setColumns('col-md-6')
        ;

        yield AssociationField::new('package', '所属套餐')
            ->setColumns('col-md-6')
        ;

        yield BooleanField::new('valid', '有效状态')
            ->renderAsSwitch(false)
        ;

        yield AssociationField::new('consumptions', '消费记录')
            ->onlyOnDetail()
        ;

        yield DateTimeField::new('createTime', '创建时间')
            ->hideOnForm()
            ->setFormat('y-MM-dd HH:mm:ss')
        ;

        yield AssociationField::new('createdBy', '创建者')
            ->onlyOnDetail()
        ;
    }

    public function configureFilters(Filters $filters): Filters
    {
        return $filters
            ->add('company')
            ->add('campaign')
            ->add('package')
            ->add('owner')
            ->add('cardNumber')
            ->add(ChoiceFilter::new('status')->setChoices([
                '初始化' => PrepaidCardStatus::INIT->value,
                '生效中' => PrepaidCardStatus::VALID->value,
                '已过期' => PrepaidCardStatus::EXPIRED->value,
                '已使用' => PrepaidCardStatus::EMPTY->value,
            ]))
            ->add(BooleanFilter::new('valid'))
            ->add(DateTimeFilter::new('bindTime'))
            ->add(DateTimeFilter::new('expireTime'))
            ->add(DateTimeFilter::new('createTime'))
        ;
    }
}

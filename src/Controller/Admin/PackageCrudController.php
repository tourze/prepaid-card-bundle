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
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\MoneyField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\UrlField;
use EasyCorp\Bundle\EasyAdminBundle\Filter\BooleanFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\ChoiceFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\DateTimeFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\NumericFilter;
use PrepaidCardBundle\Entity\Package;
use PrepaidCardBundle\Enum\PrepaidCardExpireType;
use PrepaidCardBundle\Enum\PrepaidCardType;
use Tourze\EasyAdminEnumFieldBundle\Field\EnumField;

#[AdminCrud(
    routePath: '/prepaid-card/package',
    routeName: 'prepaid_card_package'
)]
final class PackageCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Package::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('礼品卡包')
            ->setEntityLabelInPlural('礼品卡包管理')
            ->setPageTitle(Crud::PAGE_INDEX, '礼品卡包列表')
            ->setPageTitle(Crud::PAGE_NEW, '新建礼品卡包')
            ->setPageTitle(Crud::PAGE_EDIT, '编辑礼品卡包')
            ->setPageTitle(Crud::PAGE_DETAIL, '礼品卡包详情')
            ->setDefaultSort(['createTime' => 'DESC'])
            ->setSearchFields(['packageId', 'campaign.title'])
            ->showEntityActionsInlined()
            ->setFormThemes(['@EasyAdmin/crud/form_theme.html.twig'])
        ;
    }

    public function configureFields(string $pageName): iterable
    {
        yield IdField::new('id', 'ID')
            ->onlyOnIndex()
        ;

        yield TextField::new('packageId', '码包ID')
            ->setColumns('col-md-6')
            ->setRequired(true)
            ->setMaxLength(40)
            ->setHelp('唯一标识码包的ID')
        ;

        yield AssociationField::new('campaign', '所属活动')
            ->setColumns('col-md-6')
            ->setRequired(true)
        ;

        yield MoneyField::new('parValue', '面值')
            ->setColumns('col-md-6')
            ->setCurrency('CNY')
            ->setStoredAsCents(false)
            ->setHelp('礼品卡面值，可为空')
        ;

        yield IntegerField::new('quantity', '数量')
            ->setColumns('col-md-6')
            ->setRequired(true)
            ->setHelp('此包中礼品卡的数量')
        ;

        $typeField = EnumField::new('type', '卡片类型');
        $typeField->setEnumCases(PrepaidCardType::cases());
        yield $typeField
            ->setColumns('col-md-6')
            ->setRequired(true)
        ;

        yield DateTimeField::new('startTime', '卡有效起始时间')
            ->setColumns('col-md-6')
            ->setFormat('y-MM-dd HH:mm:ss')
            ->setHelp('卡片开始生效的时间')
        ;

        yield DateTimeField::new('expireTime', '卡有效截止时间')
            ->setColumns('col-md-6')
            ->setFormat('y-MM-dd HH:mm:ss')
            ->setHelp('卡片失效时间')
        ;

        yield IntegerField::new('expireDays', '余额有效期（天）')
            ->setColumns('col-md-6')
            ->setHelp('余额有效天数')
        ;

        yield DateTimeField::new('maxValidTime', '最大有效时间')
            ->setColumns('col-md-6')
            ->setFormat('y-MM-dd HH:mm:ss')
            ->setHelp('卡片最大有效时间')
        ;

        $expireTypeField = EnumField::new('expireType', '过期类型');
        $expireTypeField->setEnumCases(PrepaidCardExpireType::cases());
        yield $expireTypeField
            ->setColumns('col-md-6')
            ->setRequired(true)
        ;

        yield IntegerField::new('expireNum', '过期天数')
            ->setColumns('col-md-6')
            ->setHelp('过期相关的天数设置')
        ;

        yield UrlField::new('thumbUrl', '缩略图')
            ->setColumns('col-md-12')
            ->setHelp('礼品卡缩略图URL')
        ;

        yield BooleanField::new('valid', '有效状态')
            ->renderAsSwitch(false)
            ->setColumns('col-md-6')
        ;

        yield AssociationField::new('cards', '关联礼品卡')
            ->onlyOnDetail()
            ->setHelp('此包下的所有礼品卡')
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
            ->add('campaign')
            ->add('packageId')
            ->add(ChoiceFilter::new('type', '卡片类型')->setChoices([
                '一次性全额付款' => PrepaidCardType::ONE_TIME->value,
                '定金后期结算' => PrepaidCardType::AFTER->value,
            ]))
            ->add(ChoiceFilter::new('expireType', '过期类型')->setChoices([
                '同卡有效期' => PrepaidCardExpireType::SAME_WITH_CARD->value,
                '激活后' => PrepaidCardExpireType::AFTER_ACTIVATION->value,
            ]))
            ->add(NumericFilter::new('quantity', '数量'))
            ->add(NumericFilter::new('parValue', '面值'))
            ->add(BooleanFilter::new('valid', '有效状态'))
            ->add(DateTimeFilter::new('startTime', '卡有效起始时间'))
            ->add(DateTimeFilter::new('expireTime', '卡有效截止时间'))
            ->add(DateTimeFilter::new('createTime', '创建时间'))
        ;
    }
}

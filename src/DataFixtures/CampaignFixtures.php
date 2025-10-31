<?php

namespace PrepaidCardBundle\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Persistence\ObjectManager;
use PrepaidCardBundle\Entity\Campaign;
use PrepaidCardBundle\Entity\Company;
use Symfony\Component\DependencyInjection\Attribute\When;

#[When(env: 'dev')]
#[When(env: 'test')]
class CampaignFixtures extends Fixture implements FixtureGroupInterface, DependentFixtureInterface
{
    public const CAMPAIGN_COFFEE_REWARD = 'campaign-coffee-reward';
    public const CAMPAIGN_FASHION_SALE = 'campaign-fashion-sale';
    public const CAMPAIGN_GROCERY_VOUCHER = 'campaign-grocery-voucher';

    /**
     * @return array<string>
     */
    public static function getGroups(): array
    {
        return ['prepaid-card', 'dev'];
    }

    public function load(ObjectManager $manager): void
    {
        $starbucks = $this->getReference(CompanyFixtures::COMPANY_STARBUCKS, Company::class);
        $uniqlo = $this->getReference(CompanyFixtures::COMPANY_UNIQLO, Company::class);
        $walmart = $this->getReference(CompanyFixtures::COMPANY_WALMART, Company::class);

        $coffeeReward = new Campaign();
        $coffeeReward->setTitle('星巴克会员礼品卡活动');
        $coffeeReward->setCompany($starbucks);
        $coffeeReward->setStartTime(new \DateTimeImmutable('2024-01-01'));
        $coffeeReward->setExpireTime(new \DateTimeImmutable('2024-12-31'));
        $coffeeReward->setValid(true);
        $coffeeReward->setGiveCouponIds([1001, 1002]);
        $manager->persist($coffeeReward);

        $fashionSale = new Campaign();
        $fashionSale->setTitle('优衣库春季促销礼品卡');
        $fashionSale->setCompany($uniqlo);
        $fashionSale->setStartTime(new \DateTimeImmutable('2024-03-01'));
        $fashionSale->setExpireTime(new \DateTimeImmutable('2024-05-31'));
        $fashionSale->setValid(true);
        $manager->persist($fashionSale);

        $groceryVoucher = new Campaign();
        $groceryVoucher->setTitle('沃尔玛购物代金券活动');
        $groceryVoucher->setCompany($walmart);
        $groceryVoucher->setStartTime(new \DateTimeImmutable('2024-02-01'));
        $groceryVoucher->setExpireTime(new \DateTimeImmutable('2024-06-30'));
        $groceryVoucher->setValid(true);
        $groceryVoucher->setGiveCouponIds([2001]);
        $manager->persist($groceryVoucher);

        $manager->flush();

        $this->addReference(self::CAMPAIGN_COFFEE_REWARD, $coffeeReward);
        $this->addReference(self::CAMPAIGN_FASHION_SALE, $fashionSale);
        $this->addReference(self::CAMPAIGN_GROCERY_VOUCHER, $groceryVoucher);
    }

    /**
     * @return array<class-string<FixtureInterface>>
     */
    public function getDependencies(): array
    {
        return [
            CompanyFixtures::class,
        ];
    }
}

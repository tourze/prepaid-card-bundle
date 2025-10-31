<?php

namespace PrepaidCardBundle\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Persistence\ObjectManager;
use PrepaidCardBundle\Entity\Campaign;
use PrepaidCardBundle\Entity\Package;
use PrepaidCardBundle\Enum\PrepaidCardExpireType;
use PrepaidCardBundle\Enum\PrepaidCardType;
use Symfony\Component\DependencyInjection\Attribute\When;

#[When(env: 'dev')]
#[When(env: 'test')]
class PackageFixtures extends Fixture implements FixtureGroupInterface, DependentFixtureInterface
{
    public const PACKAGE_COFFEE_50 = 'package-coffee-50';
    public const PACKAGE_COFFEE_100 = 'package-coffee-100';
    public const PACKAGE_FASHION_200 = 'package-fashion-200';
    public const PACKAGE_GROCERY_100 = 'package-grocery-100';

    /**
     * @return array<string>
     */
    public static function getGroups(): array
    {
        return ['prepaid-card', 'dev'];
    }

    public function load(ObjectManager $manager): void
    {
        $coffeeReward = $this->getReference(CampaignFixtures::CAMPAIGN_COFFEE_REWARD, Campaign::class);
        $fashionSale = $this->getReference(CampaignFixtures::CAMPAIGN_FASHION_SALE, Campaign::class);
        $groceryVoucher = $this->getReference(CampaignFixtures::CAMPAIGN_GROCERY_VOUCHER, Campaign::class);

        $coffee50 = new Package();
        $coffee50->setCampaign($coffeeReward);
        $coffee50->setPackageId('PKG-COFFEE-50-001');
        $coffee50->setParValue('50.00');
        $coffee50->setQuantity(100);
        $coffee50->setStartTime(new \DateTimeImmutable('2024-01-01'));
        $coffee50->setExpireTime(new \DateTimeImmutable('2024-12-31'));
        $coffee50->setExpireDays(365);
        $coffee50->setType(PrepaidCardType::ONE_TIME);
        $coffee50->setExpireType(PrepaidCardExpireType::SAME_WITH_CARD);
        $coffee50->setExpireNum(0);
        $coffee50->setThumbUrl('https://images.unsplash.com/photo-1501339847302-ac426a4a7cbb?w=200');
        $coffee50->setValid(true);
        $manager->persist($coffee50);

        $coffee100 = new Package();
        $coffee100->setCampaign($coffeeReward);
        $coffee100->setPackageId('PKG-COFFEE-100-001');
        $coffee100->setParValue('100.00');
        $coffee100->setQuantity(50);
        $coffee100->setStartTime(new \DateTimeImmutable('2024-01-01'));
        $coffee100->setExpireTime(new \DateTimeImmutable('2024-12-31'));
        $coffee100->setExpireDays(365);
        $coffee100->setType(PrepaidCardType::ONE_TIME);
        $coffee100->setExpireType(PrepaidCardExpireType::SAME_WITH_CARD);
        $coffee100->setExpireNum(0);
        $coffee100->setThumbUrl('https://images.unsplash.com/photo-1495474472287-4d71bcdd2085?w=200');
        $coffee100->setValid(true);
        $manager->persist($coffee100);

        $fashion200 = new Package();
        $fashion200->setCampaign($fashionSale);
        $fashion200->setPackageId('PKG-FASHION-200-001');
        $fashion200->setParValue('200.00');
        $fashion200->setQuantity(30);
        $fashion200->setStartTime(new \DateTimeImmutable('2024-03-01'));
        $fashion200->setExpireTime(new \DateTimeImmutable('2024-05-31'));
        $fashion200->setExpireDays(90);
        $fashion200->setType(PrepaidCardType::AFTER);
        $fashion200->setExpireType(PrepaidCardExpireType::AFTER_ACTIVATION);
        $fashion200->setExpireNum(30);
        $fashion200->setThumbUrl('https://images.unsplash.com/photo-1441986300917-64674bd600d8?w=200');
        $fashion200->setValid(true);
        $manager->persist($fashion200);

        $grocery100 = new Package();
        $grocery100->setCampaign($groceryVoucher);
        $grocery100->setPackageId('PKG-GROCERY-100-001');
        $grocery100->setParValue('100.00');
        $grocery100->setQuantity(75);
        $grocery100->setStartTime(new \DateTimeImmutable('2024-02-01'));
        $grocery100->setExpireTime(new \DateTimeImmutable('2024-06-30'));
        $grocery100->setExpireDays(150);
        $grocery100->setType(PrepaidCardType::ONE_TIME);
        $grocery100->setExpireType(PrepaidCardExpireType::SAME_WITH_CARD);
        $grocery100->setExpireNum(0);
        $grocery100->setThumbUrl('https://images.unsplash.com/photo-1542838132-92c53300491e?w=200');
        $grocery100->setValid(true);
        $manager->persist($grocery100);

        $manager->flush();

        $this->addReference(self::PACKAGE_COFFEE_50, $coffee50);
        $this->addReference(self::PACKAGE_COFFEE_100, $coffee100);
        $this->addReference(self::PACKAGE_FASHION_200, $fashion200);
        $this->addReference(self::PACKAGE_GROCERY_100, $grocery100);
    }

    /**
     * @return array<class-string<FixtureInterface>>
     */
    public function getDependencies(): array
    {
        return [
            CampaignFixtures::class,
        ];
    }
}

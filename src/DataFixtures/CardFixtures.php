<?php

namespace PrepaidCardBundle\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Persistence\ObjectManager;
use PrepaidCardBundle\Entity\Campaign;
use PrepaidCardBundle\Entity\Card;
use PrepaidCardBundle\Entity\Company;
use PrepaidCardBundle\Entity\Package;
use PrepaidCardBundle\Enum\PrepaidCardStatus;
use Symfony\Component\DependencyInjection\Attribute\When;

#[When(env: 'dev')]
#[When(env: 'test')]
class CardFixtures extends Fixture implements FixtureGroupInterface, DependentFixtureInterface
{
    public const CARD_COFFEE_50_VALID = 'card-coffee-50-valid';
    public const CARD_COFFEE_100_USED = 'card-coffee-100-used';
    public const CARD_FASHION_200_INIT = 'card-fashion-200-init';
    public const CARD_GROCERY_100_EXPIRED = 'card-grocery-100-expired';

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

        $coffeeReward = $this->getReference(CampaignFixtures::CAMPAIGN_COFFEE_REWARD, Campaign::class);
        $fashionSale = $this->getReference(CampaignFixtures::CAMPAIGN_FASHION_SALE, Campaign::class);
        $groceryVoucher = $this->getReference(CampaignFixtures::CAMPAIGN_GROCERY_VOUCHER, Campaign::class);

        $coffee50Package = $this->getReference(PackageFixtures::PACKAGE_COFFEE_50, Package::class);
        $coffee100Package = $this->getReference(PackageFixtures::PACKAGE_COFFEE_100, Package::class);
        $fashion200Package = $this->getReference(PackageFixtures::PACKAGE_FASHION_200, Package::class);
        $grocery100Package = $this->getReference(PackageFixtures::PACKAGE_GROCERY_100, Package::class);

        // Skip user creation in test environment

        // 有效的咖啡卡
        $coffee50Valid = new Card();
        $coffee50Valid->setCompany($starbucks);
        $coffee50Valid->setCampaign($coffeeReward);
        $coffee50Valid->setPackage($coffee50Package);
        $coffee50Valid->setCardNumber('SB50001234567890');
        $coffee50Valid->setCardPassword('PASS1234');
        $coffee50Valid->setParValue('50.00');
        $coffee50Valid->setBalance('50.00');
        $coffee50Valid->setStatus(PrepaidCardStatus::VALID);
        $coffee50Valid->setBindTime(new \DateTimeImmutable('2024-01-15'));
        $coffee50Valid->setExpireTime(new \DateTimeImmutable('2024-12-31'));
        // $coffee50Valid->setOwner($testUser); // Skip owner in test environment
        $manager->persist($coffee50Valid);

        // 已使用的咖啡卡
        $coffee100Used = new Card();
        $coffee100Used->setCompany($starbucks);
        $coffee100Used->setCampaign($coffeeReward);
        $coffee100Used->setPackage($coffee100Package);
        $coffee100Used->setCardNumber('SB100001234567890');
        $coffee100Used->setCardPassword('PASS5678');
        $coffee100Used->setParValue('100.00');
        $coffee100Used->setBalance('0.00');
        $coffee100Used->setStatus(PrepaidCardStatus::EMPTY);
        $coffee100Used->setBindTime(new \DateTimeImmutable('2024-02-01'));
        $coffee100Used->setExpireTime(new \DateTimeImmutable('2024-12-31'));
        // $coffee100Used->setOwner($testUser); // Skip owner in test environment
        $manager->persist($coffee100Used);

        // 初始状态的时尚卡
        $fashion200Init = new Card();
        $fashion200Init->setCompany($uniqlo);
        $fashion200Init->setCampaign($fashionSale);
        $fashion200Init->setPackage($fashion200Package);
        $fashion200Init->setCardNumber('UQ200001234567890');
        $fashion200Init->setCardPassword('FASH9012');
        $fashion200Init->setParValue('200.00');
        $fashion200Init->setBalance('200.00');
        $fashion200Init->setStatus(PrepaidCardStatus::INIT);
        $fashion200Init->setExpireTime(new \DateTimeImmutable('2024-05-31'));
        $manager->persist($fashion200Init);

        // 过期的购物卡
        $grocery100Expired = new Card();
        $grocery100Expired->setCompany($walmart);
        $grocery100Expired->setCampaign($groceryVoucher);
        $grocery100Expired->setPackage($grocery100Package);
        $grocery100Expired->setCardNumber('WM100001234567890');
        $grocery100Expired->setCardPassword('SHOP3456');
        $grocery100Expired->setParValue('100.00');
        $grocery100Expired->setBalance('75.50');
        $grocery100Expired->setStatus(PrepaidCardStatus::EXPIRED);
        $grocery100Expired->setBindTime(new \DateTimeImmutable('2024-02-15'));
        $grocery100Expired->setExpireTime(new \DateTimeImmutable('2024-03-01'));
        // $grocery100Expired->setOwner($testUser); // Skip owner in test environment
        $manager->persist($grocery100Expired);

        $this->createAdditionalCards($manager, $coffee50Package, $starbucks, $coffeeReward);

        $manager->flush();

        $this->addReference(self::CARD_COFFEE_50_VALID, $coffee50Valid);
        $this->addReference(self::CARD_COFFEE_100_USED, $coffee100Used);
        $this->addReference(self::CARD_FASHION_200_INIT, $fashion200Init);
        $this->addReference(self::CARD_GROCERY_100_EXPIRED, $grocery100Expired);
    }

    private function createAdditionalCards(ObjectManager $manager, Package $package, Company $company, Campaign $campaign): void
    {
        for ($i = 1; $i <= 10; ++$i) {
            $card = new Card();
            $card->setCompany($company);
            $card->setCampaign($campaign);
            $card->setPackage($package);
            $card->setCardNumber('SB50' . str_pad((string) $i, 12, '0', STR_PAD_LEFT));
            $card->setCardPassword('PWD' . str_pad((string) $i, 4, '0', STR_PAD_LEFT));
            $card->setParValue('50.00');
            $card->setBalance('50.00');
            $card->setStatus($i <= 5 ? PrepaidCardStatus::VALID : PrepaidCardStatus::INIT);
            $card->setExpireTime(new \DateTimeImmutable('2024-12-31'));

            if ($i <= 5) {
                $card->setBindTime(new \DateTimeImmutable('2024-01-' . (10 + $i)));
                // Skip owner setting in test environment
            }

            $manager->persist($card);
        }
    }

    /**
     * @return array<class-string<FixtureInterface>>
     */
    public function getDependencies(): array
    {
        return [
            CompanyFixtures::class,
            CampaignFixtures::class,
            PackageFixtures::class,
        ];
    }
}

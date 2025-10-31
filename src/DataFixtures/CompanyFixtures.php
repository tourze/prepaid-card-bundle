<?php

namespace PrepaidCardBundle\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Persistence\ObjectManager;
use PrepaidCardBundle\Entity\Company;
use Symfony\Component\DependencyInjection\Attribute\When;

#[When(env: 'dev')]
#[When(env: 'test')]
class CompanyFixtures extends Fixture implements FixtureGroupInterface
{
    public const COMPANY_STARBUCKS = 'company-starbucks';
    public const COMPANY_UNIQLO = 'company-uniqlo';
    public const COMPANY_WALMART = 'company-walmart';

    /**
     * @return array<string>
     */
    public static function getGroups(): array
    {
        return ['prepaid-card', 'dev'];
    }

    public function load(ObjectManager $manager): void
    {
        $starbucks = new Company();
        $starbucks->setTitle('星巴克咖啡');
        $manager->persist($starbucks);

        $uniqlo = new Company();
        $uniqlo->setTitle('优衣库');
        $manager->persist($uniqlo);

        $walmart = new Company();
        $walmart->setTitle('沃尔玛购物卡');
        $manager->persist($walmart);

        $manager->flush();

        $this->addReference(self::COMPANY_STARBUCKS, $starbucks);
        $this->addReference(self::COMPANY_UNIQLO, $uniqlo);
        $this->addReference(self::COMPANY_WALMART, $walmart);
    }
}

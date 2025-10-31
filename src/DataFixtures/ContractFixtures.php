<?php

namespace PrepaidCardBundle\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Persistence\ObjectManager;
use PrepaidCardBundle\Entity\Contract;
use Symfony\Component\DependencyInjection\Attribute\When;

#[When(env: 'dev')]
#[When(env: 'test')]
class ContractFixtures extends Fixture implements FixtureGroupInterface
{
    public const CONTRACT_COFFEE_ORDER = 'contract-coffee-order';
    public const CONTRACT_FASHION_ORDER = 'contract-fashion-order';
    public const CONTRACT_GROCERY_ORDER = 'contract-grocery-order';

    /**
     * @return array<string>
     */
    public static function getGroups(): array
    {
        return ['prepaid-card', 'dev'];
    }

    public function load(ObjectManager $manager): void
    {
        $testUserId = '1001';

        $coffeeOrder = new Contract();
        $coffeeOrder->setCode('CTR-COFFEE-001');
        $coffeeOrder->setCostAmount('25.50');
        $coffeeOrder->setCreatedBy($testUserId);
        $coffeeOrder->setCreatedFromIp('192.168.1.100');
        $manager->persist($coffeeOrder);

        $fashionOrder = new Contract();
        $fashionOrder->setCode('CTR-FASHION-001');
        $fashionOrder->setCostAmount('89.90');
        $fashionOrder->setCreatedBy($testUserId);
        $fashionOrder->setCreatedFromIp('192.168.1.101');
        $manager->persist($fashionOrder);

        $groceryOrder = new Contract();
        $groceryOrder->setCode('CTR-GROCERY-001');
        $groceryOrder->setCostAmount('156.75');
        $groceryOrder->setRefundTime(new \DateTimeImmutable('2024-03-15'));
        $groceryOrder->setCreatedBy($testUserId);
        $groceryOrder->setCreatedFromIp('192.168.1.102');
        $manager->persist($groceryOrder);

        $this->createAdditionalContracts($manager, $testUserId);

        $manager->flush();

        $this->addReference(self::CONTRACT_COFFEE_ORDER, $coffeeOrder);
        $this->addReference(self::CONTRACT_FASHION_ORDER, $fashionOrder);
        $this->addReference(self::CONTRACT_GROCERY_ORDER, $groceryOrder);
    }

    private function createAdditionalContracts(ObjectManager $manager, string $testUserId): void
    {
        for ($i = 1; $i <= 5; ++$i) {
            $contract = new Contract();
            $contract->setCode('CTR-TEST-' . str_pad((string) $i, 3, '0', STR_PAD_LEFT));
            $contract->setCostAmount((string) (20.00 + $i * 10.50));
            $contract->setCreatedBy($testUserId);
            $contract->setCreatedFromIp('192.168.1.' . (200 + $i));

            if ($i > 3) {
                $contract->setRefundTime(new \DateTimeImmutable('2024-04-' . (10 + $i)));
            }

            $manager->persist($contract);
        }
    }
}

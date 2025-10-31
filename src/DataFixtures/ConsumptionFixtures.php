<?php

namespace PrepaidCardBundle\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Persistence\ObjectManager;
use PrepaidCardBundle\Entity\Card;
use PrepaidCardBundle\Entity\Consumption;
use PrepaidCardBundle\Entity\Contract;
use Symfony\Component\DependencyInjection\Attribute\When;

#[When(env: 'dev')]
#[When(env: 'test')]
class ConsumptionFixtures extends Fixture implements FixtureGroupInterface, DependentFixtureInterface
{
    public const CONSUMPTION_COFFEE_DRINK = 'consumption-coffee-drink';
    public const CONSUMPTION_FASHION_SHIRT = 'consumption-fashion-shirt';
    public const CONSUMPTION_GROCERY_FOOD = 'consumption-grocery-food';

    /**
     * @return array<string>
     */
    public static function getGroups(): array
    {
        return ['prepaid-card', 'dev'];
    }

    public function load(ObjectManager $manager): void
    {
        $coffeeCard = $this->getReference(CardFixtures::CARD_COFFEE_100_USED, Card::class);
        $groceryCard = $this->getReference(CardFixtures::CARD_GROCERY_100_EXPIRED, Card::class);

        $coffeeOrder = $this->getReference(ContractFixtures::CONTRACT_COFFEE_ORDER, Contract::class);
        $fashionOrder = $this->getReference(ContractFixtures::CONTRACT_FASHION_ORDER, Contract::class);
        $groceryOrder = $this->getReference(ContractFixtures::CONTRACT_GROCERY_ORDER, Contract::class);

        $testUserId = '1001';

        $coffeeDrink = new Consumption();
        $coffeeDrink->setCard($coffeeCard);
        $coffeeDrink->setContract($coffeeOrder);
        $coffeeDrink->setTitle('星巴克拿铁咖啡');
        $coffeeDrink->setOrderId('ORD-COFFEE-20240215-001');
        $coffeeDrink->setAmount('25.50');
        $coffeeDrink->setRefundableAmount('25.50');
        $coffeeDrink->setCreatedBy($testUserId);
        $coffeeDrink->setCreatedFromIp('192.168.1.100');
        $manager->persist($coffeeDrink);

        $fashionShirt = new Consumption();
        $fashionShirt->setCard($coffeeCard);
        $fashionShirt->setContract($fashionOrder);
        $fashionShirt->setTitle('优衣库短袖T恤');
        $fashionShirt->setOrderId('ORD-FASHION-20240301-001');
        $fashionShirt->setAmount('89.90');
        $fashionShirt->setRefundableAmount('0.00');
        $fashionShirt->setCreatedBy($testUserId);
        $fashionShirt->setCreatedFromIp('192.168.1.101');
        $manager->persist($fashionShirt);

        $groceryFood = new Consumption();
        $groceryFood->setCard($groceryCard);
        $groceryFood->setContract($groceryOrder);
        $groceryFood->setTitle('沃尔玛生鲜食品');
        $groceryFood->setOrderId('ORD-GROCERY-20240220-001');
        $groceryFood->setAmount('24.50');
        $groceryFood->setRefundableAmount('24.50');
        $groceryFood->setCreatedBy($testUserId);
        $groceryFood->setCreatedFromIp('192.168.1.102');
        $manager->persist($groceryFood);

        $this->createAdditionalConsumptions($manager, $coffeeCard, $coffeeOrder, $testUserId);

        $manager->flush();

        $this->addReference(self::CONSUMPTION_COFFEE_DRINK, $coffeeDrink);
        $this->addReference(self::CONSUMPTION_FASHION_SHIRT, $fashionShirt);
        $this->addReference(self::CONSUMPTION_GROCERY_FOOD, $groceryFood);
    }

    private function createAdditionalConsumptions(ObjectManager $manager, Card $card, Contract $contract, string $testUserId): void
    {
        $consumptionData = [
            ['星巴克美式咖啡', '15.00'],
            ['星巴克卡布奇诺', '22.00'],
            ['星巴克马卡龙', '18.50'],
            ['星巴克三明治', '28.00'],
            ['星巴克果汁', '16.00'],
        ];

        foreach ($consumptionData as $index => $data) {
            $consumption = new Consumption();
            $consumption->setCard($card);
            $consumption->setContract($contract);
            $consumption->setTitle($data[0]);
            $consumption->setOrderId('ORD-EXTRA-' . str_pad((string) ($index + 1), 3, '0', STR_PAD_LEFT));
            $consumption->setAmount($data[1]);
            $consumption->setRefundableAmount($data[1]);
            $consumption->setCreatedBy($testUserId);
            $consumption->setCreatedFromIp('192.168.1.' . (110 + $index));
            $manager->persist($consumption);
        }
    }

    /**
     * @return array<class-string<FixtureInterface>>
     */
    public function getDependencies(): array
    {
        return [
            CardFixtures::class,
            ContractFixtures::class,
        ];
    }
}

<?php

declare(strict_types=1);

namespace PrepaidCardBundle\Service;

use Knp\Menu\ItemInterface;
use PrepaidCardBundle\Entity\Campaign;
use PrepaidCardBundle\Entity\Card;
use PrepaidCardBundle\Entity\Company;
use PrepaidCardBundle\Entity\Consumption;
use PrepaidCardBundle\Entity\Contract;
use PrepaidCardBundle\Entity\Package;
use Symfony\Component\DependencyInjection\Attribute\Autoconfigure;
use Tourze\EasyAdminMenuBundle\Service\LinkGeneratorInterface;
use Tourze\EasyAdminMenuBundle\Service\MenuProviderInterface;

#[Autoconfigure(public: true)]
readonly class AdminMenu implements MenuProviderInterface
{
    public function __construct(private LinkGeneratorInterface $linkGenerator)
    {
    }

    public function __invoke(ItemInterface $item): void
    {
        if (null === $item->getChild('礼品卡管理')) {
            $item->addChild('礼品卡管理');
        }

        $prepaidCardMenu = $item->getChild('礼品卡管理');

        if (null === $prepaidCardMenu) {
            return;
        }

        // 公司管理
        $prepaidCardMenu
            ->addChild('公司管理')
            ->setUri($this->linkGenerator->getCurdListPage(Company::class))
            ->setAttribute('icon', 'fas fa-building')
        ;

        // 活动管理
        $prepaidCardMenu
            ->addChild('活动管理')
            ->setUri($this->linkGenerator->getCurdListPage(Campaign::class))
            ->setAttribute('icon', 'fas fa-calendar-alt')
        ;

        // 套餐管理
        $prepaidCardMenu
            ->addChild('套餐管理')
            ->setUri($this->linkGenerator->getCurdListPage(Package::class))
            ->setAttribute('icon', 'fas fa-box')
        ;

        // 卡片管理
        $prepaidCardMenu
            ->addChild('卡片管理')
            ->setUri($this->linkGenerator->getCurdListPage(Card::class))
            ->setAttribute('icon', 'fas fa-credit-card')
        ;

        // 预付订单管理
        $prepaidCardMenu
            ->addChild('预付订单')
            ->setUri($this->linkGenerator->getCurdListPage(Contract::class))
            ->setAttribute('icon', 'fas fa-file-contract')
        ;

        // 消费记录
        $prepaidCardMenu
            ->addChild('消费记录')
            ->setUri($this->linkGenerator->getCurdListPage(Consumption::class))
            ->setAttribute('icon', 'fas fa-receipt')
        ;
    }
}

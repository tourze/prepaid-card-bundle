<?php

namespace PrepaidCardBundle\Command;

use Carbon\Carbon;
use Doctrine\ORM\EntityManagerInterface;
use PrepaidCardBundle\Entity\Card;
use PrepaidCardBundle\Enum\PrepaidCardStatus;
use PrepaidCardBundle\Repository\CardRepository;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Tourze\Symfony\CronJob\Attribute\AsCronTask;

#[AsCronTask('* * * * *')]
#[AsCommand(name: PrepaidCardExpireCheckCommand::NAME, description: '自动过期失效')]
class PrepaidCardExpireCheckCommand extends Command
{
    public const NAME = 'prepaid-card:expire-check';

    public function __construct(
        private readonly CardRepository $cardRepository,
        private readonly EntityManagerInterface $entityManager,
    )
    {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $qb = $this->cardRepository->createQueryBuilder('a')
            ->where('a.status=:status')
            ->andWhere('a.expireTime IS NOT NULL')
            ->andWhere('a.expireTime <= :now')
            ->setParameter('status', PrepaidCardStatus::VALID)
            ->setParameter('now', Carbon::now())
            ->setMaxResults(500);
        foreach ($qb->getQuery()->getResult() as $card) {
            /* @var Card $card */
            $card->setStatus(PrepaidCardStatus::EXPIRED);
            $this->entityManager->persist($card);
        }
        $this->entityManager->flush();

        $qb = $this->cardRepository->createQueryBuilder('a')
            ->where('a.balance<=0 AND a.status=:status')
            ->setParameter('status', PrepaidCardStatus::VALID)
            ->setMaxResults(500);
        foreach ($qb->getQuery()->getResult() as $card) {
            /* @var Card $card */
            $card->setStatus(PrepaidCardStatus::EMPTY);
            $this->entityManager->persist($card);
        }
        $this->entityManager->flush();

        return Command::SUCCESS;
    }
}

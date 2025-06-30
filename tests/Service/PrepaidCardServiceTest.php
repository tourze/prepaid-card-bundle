<?php

namespace PrepaidCardBundle\Tests\Service;

use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;
use PrepaidCardBundle\Repository\CardRepository;
use PrepaidCardBundle\Service\PrepaidCardService;
use Psr\Log\LoggerInterface;

class PrepaidCardServiceTest extends TestCase
{
    private PrepaidCardService $service;
    private CardRepository $cardRepository;
    private EntityManagerInterface $entityManager;
    private LoggerInterface $logger;

    protected function setUp(): void
    {
        $this->cardRepository = $this->createMock(CardRepository::class);
        $this->entityManager = $this->createMock(EntityManagerInterface::class);
        $this->logger = $this->createMock(LoggerInterface::class);

        $this->service = new PrepaidCardService(
            $this->cardRepository,
            $this->entityManager,
            $this->logger
        );
    }

    public function testServiceCanBeInstantiated(): void
    {
        $this->assertInstanceOf(PrepaidCardService::class, $this->service);
    }
}
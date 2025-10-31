<?php

namespace PrepaidCardBundle\Entity;

use Carbon\CarbonImmutable;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use PrepaidCardBundle\Enum\PrepaidCardStatus;
use PrepaidCardBundle\Repository\CardRepository;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Serializer\Attribute\Ignore;
use Symfony\Component\Validator\Constraints as Assert;
use Tourze\Arrayable\AdminArrayInterface;
use Tourze\Arrayable\ApiArrayInterface;
use Tourze\DoctrineIndexedBundle\Attribute\IndexColumn;
use Tourze\DoctrineSnowflakeBundle\Traits\SnowflakeKeyAware;
use Tourze\DoctrineTimestampBundle\Traits\TimestampableAware;
use Tourze\DoctrineTrackBundle\Attribute\TrackColumn;
use Tourze\DoctrineUserBundle\Traits\CreatedByAware;

/**
 * @see https://blog.csdn.net/zhichaosong/article/details/120316738
 * @implements AdminArrayInterface<string, mixed>
 * @implements ApiArrayInterface<string, mixed>
 */
#[ORM\Table(name: 'ims_prepaid_card', options: ['comment' => '礼品卡'])]
#[ORM\Entity(repositoryClass: CardRepository::class)]
class Card implements ApiArrayInterface, AdminArrayInterface, \Stringable
{
    use TimestampableAware;
    use CreatedByAware;
    use SnowflakeKeyAware;

    #[ORM\ManyToOne(inversedBy: 'cards')]
    private ?Company $company = null;

    #[Assert\NotBlank]
    #[Assert\Length(max: 40)]
    #[ORM\Column(length: 40, unique: true, options: ['comment' => '卡号'])]
    private string $cardNumber;

    #[Assert\Length(max: 64)]
    #[ORM\Column(length: 64, nullable: true, options: ['comment' => '卡密'])]
    private ?string $cardPassword = null;

    #[TrackColumn]
    #[Assert\NotBlank]
    #[Assert\PositiveOrZero]
    #[Assert\Length(max: 13)]
    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2, options: ['comment' => '面值'])]
    private ?string $parValue = null;

    #[IndexColumn]
    #[TrackColumn]
    #[Assert\PositiveOrZero]
    #[Assert\Length(max: 13)]
    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2, nullable: true, options: ['comment' => '余额'])]
    private ?string $balance = null;

    #[IndexColumn]
    #[TrackColumn]
    #[Assert\DateTime]
    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true, options: ['comment' => '绑定时间'])]
    private ?\DateTimeImmutable $bindTime = null;

    #[IndexColumn]
    #[TrackColumn]
    #[Assert\DateTime]
    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true, options: ['comment' => '过期时间'])]
    private ?\DateTimeImmutable $expireTime = null;

    #[ORM\ManyToOne(targetEntity: UserInterface::class)]
    #[ORM\JoinColumn(nullable: true, onDelete: 'SET NULL')]
    private ?UserInterface $owner = null;

    #[TrackColumn]
    #[Assert\Choice(callback: [PrepaidCardStatus::class, 'cases'])]
    #[ORM\Column(length: 30, nullable: true, enumType: PrepaidCardStatus::class, options: ['comment' => '状态'])]
    private ?PrepaidCardStatus $status = null;

    /**
     * @var Collection<int, Consumption>
     */
    #[Ignore]
    #[ORM\OneToMany(mappedBy: 'card', targetEntity: Consumption::class, orphanRemoval: true)]
    private Collection $consumptions;

    #[ORM\ManyToOne(inversedBy: 'cards')]
    private ?Campaign $campaign = null;

    #[ORM\ManyToOne(inversedBy: 'cards')]
    private ?Package $package = null;

    #[IndexColumn]
    #[TrackColumn]
    #[Assert\Type(type: 'bool')]
    #[ORM\Column(type: Types::BOOLEAN, nullable: true, options: ['comment' => '有效', 'default' => 0])]
    private ?bool $valid = false;

    public function __construct()
    {
        $this->consumptions = new ArrayCollection();
    }

    public function isValid(): ?bool
    {
        return $this->valid;
    }

    public function setValid(?bool $valid): void
    {
        $this->valid = $valid;
    }

    public function getCompany(): ?Company
    {
        return $this->company;
    }

    public function setCompany(?Company $company): void
    {
        $this->company = $company;
    }

    public function getCardNumber(): string
    {
        return $this->cardNumber;
    }

    public function setCardNumber(string $cardNumber): void
    {
        $this->cardNumber = $cardNumber;
    }

    public function getCardPassword(): ?string
    {
        return $this->cardPassword;
    }

    public function setCardPassword(?string $cardPassword): void
    {
        $this->cardPassword = $cardPassword;
    }

    public function getParValue(): ?string
    {
        return $this->parValue;
    }

    public function setParValue(string $parValue): void
    {
        $this->parValue = $parValue;
    }

    public function getBindTime(): ?\DateTimeImmutable
    {
        return $this->bindTime;
    }

    public function setBindTime(?\DateTimeImmutable $bindTime): void
    {
        $this->bindTime = $bindTime;
    }

    public function getExpireTime(): ?\DateTimeImmutable
    {
        return $this->expireTime;
    }

    public function setExpireTime(?\DateTimeImmutable $expireTime): void
    {
        $this->expireTime = $expireTime;
    }

    public function getOwner(): ?UserInterface
    {
        return $this->owner;
    }

    public function setOwner(?UserInterface $owner): void
    {
        $this->owner = $owner;
    }

    public function getBalance(): ?string
    {
        return $this->balance;
    }

    public function setBalance(?string $balance): void
    {
        $this->balance = $balance;
    }

    public function getStatus(): ?PrepaidCardStatus
    {
        return $this->status;
    }

    public function setStatus(?PrepaidCardStatus $status): void
    {
        $this->status = $status;
    }

    /**
     * @return Collection<int, Consumption>
     */
    public function getConsumptions(): Collection
    {
        return $this->consumptions;
    }

    public function addConsumption(Consumption $consumption): void
    {
        if (!$this->consumptions->contains($consumption)) {
            $this->consumptions->add($consumption);
            $consumption->setCard($this);
        }
    }

    public function removeConsumption(Consumption $consumption): void
    {
        $this->consumptions->removeElement($consumption);
    }

    /**
     * @return array<string, mixed>
     */
    public function retrieveApiArray(): array
    {
        return [
            'id' => $this->getId(),
            'createTime' => $this->getCreateTime()?->format('Y-m-d H:i:s'),
            'updateTime' => $this->getUpdateTime()?->format('Y-m-d H:i:s'),
            'cardNumber' => $this->getCardNumber(),
            // 'cardPassword' => $this->getCardPassword(),
            'parValue' => $this->getParValue(),
            'balance' => $this->getBalance(),
            'bindTime' => $this->getBindTime()?->format('Y-m-d H:i:s'),
            'expireTime' => $this->getExpireTime()?->format('Y-m-d H:i:s'),
            'status' => $this->getStatus()?->toSelectItem(),
            'campaign' => $this->getCampaign()?->retrieveApiArray(),
            'package' => $this->getPackage()?->retrieveApiArray(),
        ];
    }

    public function getCampaign(): ?Campaign
    {
        return $this->campaign;
    }

    public function setCampaign(?Campaign $campaign): void
    {
        $this->campaign = $campaign;
    }

    public function checkStatus(): void
    {
        $now = CarbonImmutable::now();
        $expireTime = $this->getExpireTime();

        if (null !== $expireTime && $now->greaterThan($expireTime)) {
            $this->setStatus(PrepaidCardStatus::EXPIRED);
        } else {
            if ($this->getBalance() > 0) {
                $this->setStatus(PrepaidCardStatus::VALID);
            } else {
                $this->setStatus(PrepaidCardStatus::EMPTY);
            }
        }
    }

    public function getPackage(): ?Package
    {
        return $this->package;
    }

    public function setPackage(?Package $package): void
    {
        $this->package = $package;
    }

    /**
     * @return array<string, mixed>
     */
    public function retrieveAdminArray(): array
    {
        return [
            'id' => $this->getId(),
            'createTime' => $this->getCreateTime()?->format('Y-m-d H:i:s'),
            'updateTime' => $this->getUpdateTime()?->format('Y-m-d H:i:s'),
            'cardNumber' => $this->getCardNumber(),
            'cardPassword' => $this->getCardPassword(),
            'parValue' => $this->getParValue(),
            'balance' => $this->getBalance(),
            'bindTime' => $this->getBindTime()?->format('Y-m-d H:i:s'),
            'expireTime' => $this->getExpireTime()?->format('Y-m-d H:i:s'),
            'status' => $this->getStatus()?->value,
        ];
    }

    public function __toString(): string
    {
        return (string) $this->getId();
    }
}

<?php

namespace PrepaidCardBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use PrepaidCardBundle\Repository\CampaignRepository;
use Symfony\Component\Serializer\Attribute\Ignore;
use Symfony\Component\Validator\Constraints as Assert;
use Tourze\Arrayable\AdminArrayInterface;
use Tourze\Arrayable\ApiArrayInterface;
use Tourze\DoctrineIndexedBundle\Attribute\IndexColumn;
use Tourze\DoctrineSnowflakeBundle\Traits\SnowflakeKeyAware;
use Tourze\DoctrineTimestampBundle\Traits\TimestampableAware;
use Tourze\DoctrineTrackBundle\Attribute\TrackColumn;
use Tourze\DoctrineUserBundle\Traits\BlameableAware;

/**
 * @implements AdminArrayInterface<string, mixed>
 * @implements ApiArrayInterface<string, mixed>
 */
#[ORM\Table(name: 'ims_prepaid_campaign', options: ['comment' => '礼品卡活动'])]
#[ORM\Entity(repositoryClass: CampaignRepository::class)]
class Campaign implements AdminArrayInterface, ApiArrayInterface, \Stringable
{
    use TimestampableAware;
    use BlameableAware;
    use SnowflakeKeyAware;

    #[Assert\NotBlank]
    #[Assert\Length(max: 100)]
    #[ORM\Column(length: 100, options: ['comment' => '活动名称'])]
    private string $title;

    /**
     * @var Collection<int, Package>
     */
    #[ORM\OneToMany(mappedBy: 'campaign', targetEntity: Package::class, cascade: ['persist', 'remove'], orphanRemoval: true)]
    private Collection $packages;

    #[ORM\ManyToOne(inversedBy: 'campaigns')]
    private ?Company $company = null;

    #[Assert\DateTime]
    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true, options: ['comment' => '活动生效时间'])]
    private ?\DateTimeImmutable $startTime = null;

    #[Assert\DateTime]
    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true, options: ['comment' => '活动结束时间'])]
    private ?\DateTimeImmutable $expireTime = null;

    /**
     * @var Collection<int, Card>
     */
    #[Ignore]
    #[ORM\OneToMany(mappedBy: 'campaign', targetEntity: Card::class)]
    private Collection $cards;

    /**
     * @var array<int>|null
     */
    #[Assert\Type(type: 'array')]
    #[ORM\Column(type: Types::JSON, nullable: true, options: ['comment' => '赠送优惠券ID'])]
    private ?array $giveCouponIds = [];

    #[IndexColumn]
    #[TrackColumn]
    #[Assert\Type(type: 'bool')]
    #[ORM\Column(type: Types::BOOLEAN, nullable: true, options: ['comment' => '有效', 'default' => 0])]
    private ?bool $valid = false;

    public function __construct()
    {
        $this->cards = new ArrayCollection();
        $this->packages = new ArrayCollection();
    }

    public function isValid(): ?bool
    {
        return $this->valid;
    }

    public function setValid(?bool $valid): void
    {
        $this->valid = $valid;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function setTitle(string $title): void
    {
        $this->title = $title;
    }

    /**
     * @return Collection<int, Package>
     */
    public function getPackages(): Collection
    {
        return $this->packages;
    }

    public function addPackage(Package $package): void
    {
        if (!$this->packages->contains($package)) {
            $this->packages->add($package);
            $package->setCampaign($this);
        }
    }

    public function removePackage(Package $package): void
    {
        if ($this->packages->removeElement($package)) {
            // set the owning side to null (unless already changed)
            if ($package->getCampaign() === $this) {
                $package->setCampaign(null);
            }
        }
    }

    public function getCompany(): ?Company
    {
        return $this->company;
    }

    public function setCompany(?Company $company): void
    {
        $this->company = $company;
    }

    public function getStartTime(): ?\DateTimeImmutable
    {
        return $this->startTime;
    }

    public function setStartTime(?\DateTimeImmutable $startTime): void
    {
        $this->startTime = $startTime;
    }

    public function getExpireTime(): ?\DateTimeImmutable
    {
        return $this->expireTime;
    }

    public function setExpireTime(?\DateTimeImmutable $expireTime): void
    {
        $this->expireTime = $expireTime;
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
            'title' => $this->getTitle(),
            'startTime' => $this->getStartTime()?->format('Y-m-d H:i:s'),
            'expireTime' => $this->getExpireTime()?->format('Y-m-d H:i:s'),
            'valid' => $this->isValid(),
            'giveCouponIds' => $this->getGiveCouponIds(),
            'company' => $this->getCompany()?->retrieveAdminArray(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function retrieveApiArray(): array
    {
        return [
            'id' => $this->getId(),
            'title' => $this->getTitle(),
        ];
    }

    /**
     * @return Collection<int, Card>
     */
    public function getCards(): Collection
    {
        return $this->cards;
    }

    public function addCard(Card $card): void
    {
        if (!$this->cards->contains($card)) {
            $this->cards->add($card);
            $card->setCampaign($this);
        }
    }

    public function removeCard(Card $card): void
    {
        if ($this->cards->removeElement($card)) {
            // set the owning side to null (unless already changed)
            if ($card->getCampaign() === $this) {
                $card->setCampaign(null);
            }
        }
    }

    /**
     * @return array<int>
     */
    public function getGiveCouponIds(): array
    {
        if (null === $this->giveCouponIds) {
            return [];
        }

        return $this->giveCouponIds;
    }

    /**
     * @param array<int>|null $giveCouponIds
     */
    public function setGiveCouponIds(?array $giveCouponIds): void
    {
        $this->giveCouponIds = $giveCouponIds;
    }

    public function __toString(): string
    {
        return (string) $this->getId();
    }
}

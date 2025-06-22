<?php

namespace PrepaidCardBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use PrepaidCardBundle\Repository\CampaignRepository;
use Symfony\Component\Serializer\Attribute\Ignore;
use Tourze\Arrayable\AdminArrayInterface;
use Tourze\Arrayable\ApiArrayInterface;
use Tourze\DoctrineIndexedBundle\Attribute\IndexColumn;
use Tourze\DoctrineSnowflakeBundle\Service\SnowflakeIdGenerator;
use Tourze\DoctrineTimestampBundle\Traits\TimestampableAware;
use Tourze\DoctrineTrackBundle\Attribute\TrackColumn;
use Tourze\DoctrineUserBundle\Traits\BlameableAware;

#[ORM\Table(name: 'ims_prepaid_campaign', options: ['comment' => '礼品卡活动'])]
#[ORM\Entity(repositoryClass: CampaignRepository::class)]
class Campaign implements AdminArrayInterface, ApiArrayInterface
, \Stringable
{
    use TimestampableAware;
    use BlameableAware;
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(SnowflakeIdGenerator::class)]
    #[ORM\Column(type: Types::BIGINT, nullable: false, options: ['comment' => 'ID'])]
    private ?string $id = null;

    #[ORM\Column(length: 100, options: ['comment' => '活动名称'])]
    private string $title;

    /**
     * @var Collection<int, Package>
     */
    #[ORM\OneToMany(mappedBy: 'campaign', targetEntity: Package::class, cascade: ['persist', 'remove'], orphanRemoval: true)]
    private Collection $packages;

    #[ORM\ManyToOne(inversedBy: 'campaigns')]
    private ?Company $company = null;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true, options: ['comment' => '活动生效时间'])]
    private ?\DateTimeImmutable $startTime = null;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true, options: ['comment' => '活动结束时间'])]
    private ?\DateTimeImmutable $expireTime = null;

    /**
     * @var Collection<int, Card>
     */
    #[Ignore]
    #[ORM\OneToMany(mappedBy: 'campaign', targetEntity: Card::class)]
    private Collection $cards;

    #[ORM\Column(type: Types::JSON, nullable: true, options: ['comment' => '赠送优惠券ID'])]
    private ?array $giveCouponIds = [];

    #[IndexColumn]
    #[TrackColumn]
    #[ORM\Column(type: Types::BOOLEAN, nullable: true, options: ['comment' => '有效', 'default' => 0])]
    private ?bool $valid = false;


    public function __construct()
    {
        $this->cards = new ArrayCollection();
        $this->packages = new ArrayCollection();
    }

    public function getId(): ?string
    {
        return $this->id;
    }


    public function isValid(): ?bool
    {
        return $this->valid;
    }

    public function setValid(?bool $valid): self
    {
        $this->valid = $valid;

        return $this;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function setTitle(string $title): static
    {
        $this->title = $title;

        return $this;
    }

    /**
     * @return Collection<int, Package>
     */
    public function getPackages(): Collection
    {
        return $this->packages;
    }

    public function addPackage(Package $package): static
    {
        if (!$this->packages->contains($package)) {
            $this->packages->add($package);
            $package->setCampaign($this);
        }

        return $this;
    }

    public function removePackage(Package $package): static
    {
        if ($this->packages->removeElement($package)) {
            // set the owning side to null (unless already changed)
            if ($package->getCampaign() === $this) {
                $package->setCampaign(null);
            }
        }

        return $this;
    }

    public function getCompany(): ?Company
    {
        return $this->company;
    }

    public function setCompany(?Company $company): static
    {
        $this->company = $company;

        return $this;
    }

    public function getStartTime(): ?\DateTimeImmutable
    {
        return $this->startTime;
    }

    public function setStartTime(?\DateTimeImmutable $startTime): static
    {
        $this->startTime = $startTime;

        return $this;
    }

    public function getExpireTime(): ?\DateTimeImmutable
    {
        return $this->expireTime;
    }

    public function setExpireTime(?\DateTimeImmutable $expireTime): static
    {
        $this->expireTime = $expireTime;

        return $this;
    }

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

    public function addCard(Card $card): static
    {
        if (!$this->cards->contains($card)) {
            $this->cards->add($card);
            $card->setCampaign($this);
        }

        return $this;
    }

    public function removeCard(Card $card): static
    {
        if ($this->cards->removeElement($card)) {
            // set the owning side to null (unless already changed)
            if ($card->getCampaign() === $this) {
                $card->setCampaign(null);
            }
        }

        return $this;
    }

    public function getGiveCouponIds(): array
    {
        if ($this->giveCouponIds === null) {
            return [];
        }

        return $this->giveCouponIds;
    }

    public function setGiveCouponIds(?array $giveCouponIds): self
    {
        $this->giveCouponIds = $giveCouponIds;

        return $this;
    }
    public function __toString(): string
    {
        return (string) $this->getId();
    }
}

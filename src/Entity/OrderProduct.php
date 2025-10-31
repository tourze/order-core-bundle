<?php

namespace OrderCoreBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use OrderCoreBundle\Repository\OrderProductRepository;
use OrderCoreBundle\Service\ContractPriceService;
use OrderCoreBundle\Service\PriceCalculationHelper;
use OrderCoreBundle\Service\PriceFormatter;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Serializer\Attribute\Ignore;
use Symfony\Component\Validator\Constraints as Assert;
use Tourze\Arrayable\ApiArrayInterface;
use Tourze\Arrayable\PlainArrayInterface;
use Tourze\DoctrineIndexedBundle\Attribute\IndexColumn;
use Tourze\DoctrineIpBundle\Traits\IpTraceableAware;
use Tourze\DoctrinePrecisionBundle\Attribute\PrecisionColumn;
use Tourze\DoctrineTimestampBundle\Traits\TimestampableAware;
use Tourze\DoctrineTrackBundle\Attribute\TrackColumn;
use Tourze\DoctrineUserBundle\Traits\BlameableAware;
use Tourze\LockServiceBundle\Model\LockEntity;
use Tourze\ProductCoreBundle\Entity\Sku;
use Tourze\ProductCoreBundle\Entity\Spu;

/**
 * 订单商品
 *
 * TODO 不同类型的产品，输入项目应该会有区别的喔，也就是这个应该还有一层联动或下级数据。也有一种办法，就是将这里的信息提到订单那一层去
 * TODO 一个商品，价格信息可能有多条的，现在是只支持一个币种的数据，不支持类似"积分+人民币"这种设置
 *
 * @implements PlainArrayInterface<string, mixed>
 * @implements ApiArrayInterface<string, mixed>
 *
 * @see https://blog.csdn.net/liwanchunxidian/article/details/83165420
 */
#[ORM\Entity(repositoryClass: OrderProductRepository::class)]
#[ORM\Table(name: 'order_contract_product', options: ['comment' => '订单合同产品表'])]
#[ORM\UniqueConstraint(name: 'order_product_idx_uniq', columns: ['contract_id', 'sku_id'])]
class OrderProduct implements \Stringable, PlainArrayInterface, ApiArrayInterface, LockEntity
{
    use TimestampableAware;
    use BlameableAware;
    use IpTraceableAware;

    #[Groups(groups: ['restful_read', 'api_tree', 'admin_curd', 'api_list'])]
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: Types::INTEGER, options: ['comment' => 'ID'])]
    private int $id = 0;

    #[IndexColumn]
    #[TrackColumn]
    #[Groups(groups: ['admin_curd', 'restful_read', 'restful_read', 'restful_write'])]
    #[Assert\Type(type: 'bool')]
    #[ORM\Column(type: Types::BOOLEAN, nullable: true, options: ['comment' => '有效', 'default' => 0])]
    private ?bool $valid = false;

    #[Ignore]
    #[ORM\ManyToOne(targetEntity: Contract::class, cascade: ['persist'], inversedBy: 'products')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private ?Contract $contract = null;

    #[Groups(groups: ['restful_read', 'admin_curd'])]
    #[ORM\ManyToOne(targetEntity: Spu::class)]
    #[ORM\JoinColumn(onDelete: 'SET NULL')]
    private ?Spu $spu = null;

    #[Groups(groups: ['restful_read', 'admin_curd'])]
    #[ORM\ManyToOne(targetEntity: Sku::class)]
    #[ORM\JoinColumn(nullable: true)]
    private ?Sku $sku = null;

    #[Assert\NotBlank]
    #[Assert\Length(max: 10)]
    #[Assert\Currency]
    #[TrackColumn]
    #[Groups(groups: ['restful_read'])]
    #[ORM\Column(type: Types::STRING, length: 10, options: ['default' => 'CNY', 'comment' => '币种'])]
    private string $currency = 'CNY';

    #[Assert\PositiveOrZero]
    #[Assert\Length(max: 23, groups: ['create', 'update'])]
    #[PrecisionColumn]
    #[TrackColumn]
    #[Groups(groups: ['restful_read'])]
    #[ORM\Column(type: Types::DECIMAL, precision: 20, scale: 2, nullable: true, options: ['comment' => '售价'])]
    private ?string $price = null;

    #[Assert\Positive]
    #[TrackColumn]
    #[Groups(groups: ['restful_read', 'admin_curd'])]
    #[ORM\Column(type: Types::INTEGER, options: ['default' => 1, 'comment' => '数量'])]
    private int $quantity = 1;

    #[Assert\Length(max: 100)]
    #[TrackColumn]
    #[Groups(groups: ['admin_curd'])]
    #[ORM\Column(type: Types::STRING, length: 100, nullable: true, options: ['comment' => '备注'])]
    private ?string $remark = null;

    /**
     * @var Collection<int, OrderPrice>
     */
    #[Ignore]
    #[Groups(groups: ['restful_read'])]
    #[ORM\OneToMany(mappedBy: 'product', targetEntity: OrderPrice::class, fetch: 'EXTRA_LAZY')]
    private Collection $prices;

    #[TrackColumn]
    #[Assert\Length(max: 60)]
    #[ORM\Column(length: 60, nullable: true, options: ['comment' => '产品来源'])]
    private ?string $source = null;

    #[Groups(groups: ['restful_read'])]
    #[Assert\Length(max: 30)]
    #[ORM\Column(length: 30, nullable: true, options: ['comment' => '单位'])]
    private ?string $skuUnit = null;

    #[Groups(groups: ['restful_read'])]
    #[Assert\Type(type: '\DateTimeInterface')]
    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true, options: ['comment' => '取消时间'])]
    private ?\DateTimeInterface $cancelTime = null;

    #[Groups(groups: ['restful_read'])]
    #[Assert\Length(max: 65535)]
    #[ORM\Column(type: Types::TEXT, nullable: true, options: ['comment' => '取消原因'])]
    private ?string $cancelReason = null;

    #[Groups(groups: ['restful_read'])]
    #[Assert\Type(type: 'bool')]
    #[ORM\Column(nullable: true, options: ['comment' => '审批状态'])]
    private ?bool $audited = null;

    #[Groups(groups: ['restful_read'])]
    #[Assert\Type(type: '\DateTimeInterface')]
    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true, options: ['comment' => '审批通过时间'])]
    private ?\DateTimeInterface $auditPassTime = null;

    #[Groups(groups: ['restful_read'])]
    #[Assert\Type(type: '\DateTimeInterface')]
    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true, options: ['comment' => '审批拒绝时间'])]
    private ?\DateTimeInterface $auditRejectTime = null;

    #[Groups(groups: ['restful_read'])]
    #[Assert\Type(type: 'int')]
    #[Assert\PositiveOrZero]
    #[ORM\Column(nullable: true, options: ['comment' => '发货期限'])]
    private ?int $skuDispatchPeriod = null;

    #[Groups(groups: ['restful_read'])]
    #[Assert\Length(max: 255)]
    #[ORM\Column(length: 255, nullable: true, options: ['comment' => 'SPU标题'])]
    private ?string $spuTitle = null;

    #[Assert\Type(type: '\DateTimeInterface')]
    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true, options: ['comment' => '货物收齐时间'])]
    private ?\DateTimeInterface $finishReceiveTime = null;

    #[Assert\Type(type: '\DateTimeInterface')]
    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true, options: ['comment' => '账单确认时间'])]
    private ?\DateTimeInterface $billConfirmTime = null;

    #[Assert\Type(type: '\DateTimeInterface')]
    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true, options: ['comment' => '账单取消时间'])]
    private ?\DateTimeInterface $billCancelTime = null;

    public function __construct()
    {
        $this->prices = new ArrayCollection();
    }

    public function __toString(): string
    {
        if (null === $this->getId()) {
            return '';
        }

        if (null === $this->getSku()) {
            return '未保存商品';
        }

        // 简化：直接显示基本信息，不做复杂价格计算
        return sprintf(
            '%s x %d%s',
            $this->getSku()->getFullName(),
            $this->getQuantity(),
            $this->getSku()->getUnit()
        );
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getSku(): ?Sku
    {
        return $this->sku;
    }

    public function setSku(?Sku $sku): void
    {
        $this->sku = $sku;
    }

    /**
     * @return Collection<int, OrderPrice>
     */
    public function getPrices(): Collection
    {
        return $this->prices;
    }

    /**
     * 不含税价格 - 委托给服务层
     */
    #[Groups(groups: ['restful_read'])]
    public function getDisplayPrice(): string
    {
        return $this->getPriceService()->getDisplayPriceFromCollection($this->prices);
    }

    /**
     * @deprecated 不要继续使用
     */
    public function getCurrency(): string
    {
        return $this->currency;
    }

    /**
     * @deprecated 不要继续使用
     */
    public function setCurrency(string $currency): void
    {
        $this->currency = $currency;
    }

    public function getQuantity(): int
    {
        return $this->quantity;
    }

    public function setQuantity(int $quantity): void
    {
        $this->quantity = $quantity;
    }

    public function setValid(?bool $valid): void
    {
        $this->valid = $valid;
    }

    /**
     * 应发货数量
     * 等于 总数量 - 已发货数量
     */
    #[Groups(groups: ['restful_read', 'admin_curd'])]
    public function getSendQuantity(): int
    {
        if (true !== $this->isValid()) {
            return 0;
        }
        $rs = $this->getQuantity() - 0; // 发货数量默认为0，实际需通过OrderProductDeliveryService获取

        return max($rs, 0);
    }

    public function isValid(): ?bool
    {
        return $this->valid;
    }

    /**
     * 已发货数量
     *
     * @deprecated 发货功能已移到deliver-order-bundle，请使用 OrderProductDeliveryService::getDeliverQuantity()
     */
    #[Groups(groups: ['restful_read', 'admin_curd'])]
    public function getDeliverQuantity(): int
    {
        return 0;
    }

    public function getContract(): ?Contract
    {
        return $this->contract;
    }

    public function setContract(?Contract $contract): void
    {
        $this->contract = $contract;
    }

    /**
     * 发货状态
     */
    #[Groups(groups: ['restful_read'])]
    public function getDeliverState(): ?string
    {
        $totalCount = $this->getQuantity();
        $sentCount = 0; // 发货数量默认为0，实际需通过OrderProductDeliveryService获取
        $receivedCount = 0; // 收货数量默认为0，实际需通过OrderProductDeliveryService获取

        return $this->determineDeliverState($totalCount, $sentCount, $receivedCount);
    }

    private function determineDeliverState(int $totalCount, int $sentCount, int $receivedCount): ?string
    {
        if ($totalCount === $sentCount && $sentCount === $receivedCount) {
            return '已完成';
        }
        if ($receivedCount > 0) {
            return '已收货';
        }
        if ($sentCount > 0) {
            return $sentCount === $totalCount ? '已发货' : '部分发货';
        }

        return null;
    }

    /**
     * 已收货数量（商品维度）
     *
     * @deprecated 发货功能已移到deliver-order-bundle，请使用 OrderProductDeliveryService::getReceivedQuantity()
     */
    #[Groups(groups: ['restful_read', 'admin_curd'])]
    public function getReceivedQuantity(): int
    {
        return 0;
    }

    /**
     * 不含税价格（纯数值）
     */
    public function getNumericPrice(): string
    {
        return $this->getPriceService()->getDisplayPriceFromCollection($this->prices);
    }

    /**
     * 计算指定币种的不含税价格
     */
    public function sumPriceByCurrency(string $currency): float
    {
        return $this->getPriceService()->sumPriceByCurrency($this->prices, $currency);
    }

    /**
     * 计算指定币种的含税价格
     */
    public function sumTaxPriceByCurrency(string $currency): float
    {
        return $this->getPriceService()->sumTaxPriceByCurrency($this->prices, $currency);
    }

    public function addPrice(OrderPrice $price): void
    {
        if (!$this->prices->contains($price)) {
            $this->prices->add($price);
            $price->setProduct($this);
        }
    }

    public function removePrice(OrderPrice $price): void
    {
        if ($this->prices->removeElement($price)) {
            if ($price->getProduct() === $this) {
                $price->setProduct(null);
            }
        }
    }

    public function getSource(): ?string
    {
        return $this->source;
    }

    public function setSource(?string $source): void
    {
        $this->source = $source;
    }

    public function isAudited(): ?bool
    {
        return $this->audited;
    }

    public function setAudited(?bool $audited): void
    {
        $this->audited = $audited;
    }

    public function getAuditPassTime(): ?\DateTimeInterface
    {
        return $this->auditPassTime;
    }

    public function setAuditPassTime(?\DateTimeInterface $auditPassTime): void
    {
        $this->auditPassTime = $auditPassTime;
    }

    public function getAuditRejectTime(): ?\DateTimeInterface
    {
        return $this->auditRejectTime;
    }

    public function setAuditRejectTime(?\DateTimeInterface $auditRejectTime): void
    {
        $this->auditRejectTime = $auditRejectTime;
    }

    /**
     * 总价（含税）
     */
    public function getTotalTaxPrice(): float
    {
        return $this->getPriceService()->getTotalTaxPrice($this->prices);
    }

    /**
     * 总价（未含税）
     */
    public function getTotalPrice(): float
    {
        return $this->getPriceService()->getTotalPrice($this->prices);
    }

    /**
     * 总税费
     */
    public function getTotalTax(): float
    {
        return $this->getPriceService()->getTotalTax($this->prices);
    }

    /**
     * 单价（未含税）（纯数值）
     */
    public function getNumericUnitPrice(): string
    {
        return $this->getPriceService()->getDisplayUnitPrice($this->prices, $this->quantity);
    }

    /**
     * 计算税率
     */
    public function getTaxRate(): float
    {
        return $this->getPriceService()->getTaxRate($this->prices);
    }

    /**
     * 最近一次发货时间
     *
     * @deprecated 发货功能已移到deliver-order-bundle，请使用 OrderProductDeliveryService::getLastDeliverTime()
     */
    public function getLastDeliverTime(): ?\DateTimeInterface
    {
        return null;
    }

    /**
     * 最近一次收货时间
     *
     * @deprecated 发货功能已移到deliver-order-bundle，请使用 OrderProductDeliveryService::getLastReceivedTime()
     */
    public function getLastReceiveTime(): ?\DateTimeInterface
    {
        return null;
    }

    public function getSkuDispatchPeriod(): ?int
    {
        return $this->skuDispatchPeriod;
    }

    public function setSkuDispatchPeriod(?int $skuDispatchPeriod): void
    {
        $this->skuDispatchPeriod = $skuDispatchPeriod;
    }

    public function getSpuTitle(): ?string
    {
        if ((null === $this->spuTitle || '' === $this->spuTitle) && null !== $this->getSpu()) {
            $this->spuTitle = $this->getSpu()->getTitle();
        }

        return $this->spuTitle;
    }

    public function setSpuTitle(?string $spuTitle): void
    {
        $this->spuTitle = $spuTitle;
    }

    public function getSpu(): ?Spu
    {
        if (null === $this->spu && null !== $this->getSku()) {
            $this->spu = $this->getSku()->getSpu();
        }

        return $this->spu;
    }

    public function setSpu(?Spu $spu): void
    {
        $this->spu = $spu;
    }

    public function getFinishReceiveTime(): ?\DateTimeInterface
    {
        return $this->finishReceiveTime;
    }

    public function setFinishReceiveTime(?\DateTimeInterface $finishReceiveTime): void
    {
        $this->finishReceiveTime = $finishReceiveTime;
    }

    public function getBillConfirmTime(): ?\DateTimeInterface
    {
        return $this->billConfirmTime;
    }

    public function setBillConfirmTime(?\DateTimeInterface $billConfirmTime): void
    {
        $this->billConfirmTime = $billConfirmTime;
    }

    public function getBillCancelTime(): ?\DateTimeInterface
    {
        return $this->billCancelTime;
    }

    public function setBillCancelTime(?\DateTimeInterface $billCancelTime): void
    {
        $this->billCancelTime = $billCancelTime;
    }

    /**
     * @return array<string, mixed>
     */
    public function retrieveApiArray(): array
    {
        return $this->retrievePlainArray();
    }

    /**
     * @return array<string, mixed>
     */
    public function retrievePlainArray(): array
    {
        return [
            'id' => $this->getId(),
            'createTime' => $this->getCreateTime()?->format('Y-m-d H:i:s'),
            'updateTime' => $this->getUpdateTime()?->format('Y-m-d H:i:s'),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function retrieveCheckoutArray(): array
    {
        return [
            'id' => $this->getId(),
            'spu' => $this->retrieveCheckoutArrayFromObject($this->getSpu()),
            'sku' => $this->getSku()?->retrieveCheckoutArray(),
            'currency' => $this->getCurrencyFromFirstPrice(),
            'price' => $this->getDisplayPrice(),
            'quantity' => $this->getQuantity(),
            'remark' => $this->getRemark(),
            'prices' => $this->mapPricesToArray(),
            'skuUnit' => $this->getSkuUnit(),
            'cancelTime' => $this->getCancelTime()?->format('Y-m-d H:i:s'),
            'cancelReason' => $this->getCancelReason(),
            'displayPrice' => $this->getDisplayPrice(),
            'displayTaxPrice' => $this->getDisplayTaxPrice(),
            'displayUnitPrice' => $this->getDisplayUnitPrice(),
            'displayUnitTaxPrice' => $this->getDisplayUnitTaxPrice(),
            'saleUnitPrice' => $this->getSaleUnitPrice(),
        ];
    }

    private function getCurrencyFromFirstPrice(): string
    {
        $firstPrice = $this->getPrices()->first();

        return (false !== $firstPrice) ? $firstPrice->getCurrency() : 'CNY';
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function mapPricesToArray(): array
    {
        $prices = [];
        foreach ($this->getPrices() as $price) {
            $prices[] = $price->retrieveCheckoutArray();
        }

        return $prices;
    }

    /**
     * @deprecated 不要继续使用
     */
    public function getPrice(): ?string
    {
        return $this->price;
    }

    /**
     * @deprecated 不要继续使用
     */
    public function setPrice(string $price): void
    {
        $this->price = $price;
    }

    public function getRemark(): ?string
    {
        return $this->remark;
    }

    public function setRemark(?string $remark): void
    {
        $this->remark = $remark;
    }

    public function getSkuUnit(): ?string
    {
        if ($this->shouldUpdateSkuUnit()) {
            $sku = $this->getSku();
            if (null !== $sku) {
                $this->setSkuUnit($sku->getUnit());
            }
        }

        return $this->skuUnit;
    }

    private function shouldUpdateSkuUnit(): bool
    {
        return (null === $this->skuUnit || '' === $this->skuUnit)
            && null !== $this->getSku()
            && null !== $this->getSku()->getUnit()
            && '' !== $this->getSku()->getUnit();
    }

    public function setSkuUnit(?string $skuUnit): void
    {
        $this->skuUnit = $skuUnit;
    }

    public function getCancelTime(): ?\DateTimeInterface
    {
        return $this->cancelTime;
    }

    public function setCancelTime(?\DateTimeInterface $cancelTime): void
    {
        $this->cancelTime = $cancelTime;
    }

    public function getCancelReason(): ?string
    {
        return $this->cancelReason;
    }

    public function setCancelReason(?string $cancelReason): void
    {
        $this->cancelReason = $cancelReason;
    }

    /**
     * 含税价格
     */
    #[Groups(groups: ['restful_read'])]
    public function getDisplayTaxPrice(): string
    {
        return $this->getPriceService()->getDisplayTaxPriceFromCollection($this->prices);
    }

    /**
     * 单价（未含税）
     */
    #[Groups(groups: ['restful_read'])]
    public function getDisplayUnitPrice(): string
    {
        return $this->getPriceService()->getDisplayUnitPrice($this->prices, $this->quantity);
    }

    /**
     * 单价（含税）
     */
    #[Groups(groups: ['restful_read'])]
    public function getDisplayUnitTaxPrice(): string
    {
        return $this->getPriceService()->getDisplayUnitTaxPrice($this->prices, $this->quantity);
    }

    /**
     * 返回各币种的单价(仅售价)
     * @return array<string, mixed>
     */
    #[Groups(groups: ['restful_read'])]
    public function getSaleUnitPrice(): array
    {
        $contractPrices = $this->contract?->getPrices() ?? new ArrayCollection();

        return $this->getPriceService()->getSaleUnitPrice($this, $contractPrices);
    }

    public function retrieveLockResource(): string
    {
        return "order_contract_product_{$this->getId()}";
    }

    /**
     * 获取价格服务实例 - 延迟初始化
     */
    private function getPriceService(): ContractPriceService
    {
        /** @var ContractPriceService|null $service */
        static $service = null;
        if (null === $service) {
            $formatter = new PriceFormatter();
            $service = new ContractPriceService($formatter);
        }

        return $service;
    }

    /**
     * 安全获取对象的 retrieveCheckoutArray() 方法结果
     * @return array<string, mixed>|null
     */
    private function retrieveCheckoutArrayFromObject(?object $object): ?array
    {
        if (null === $object) {
            return null;
        }

        try {
            $reflectionClass = new \ReflectionClass($object);
            if ($reflectionClass->hasMethod('retrieveCheckoutArray')) {
                $method = $reflectionClass->getMethod('retrieveCheckoutArray');

                /** @var array<string, mixed>|null */
                return $method->invoke($object);
            }
        } catch (\ReflectionException $e) {
            // 方法不存在，返回 null
        }

        return null;
    }
}

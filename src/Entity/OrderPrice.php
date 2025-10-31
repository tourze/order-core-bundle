<?php

namespace OrderCoreBundle\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use OrderCoreBundle\Repository\OrderPriceRepository;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Serializer\Attribute\Ignore;
use Symfony\Component\Validator\Constraints as Assert;
use Tourze\DoctrineIpBundle\Traits\IpTraceableAware;
use Tourze\DoctrinePrecisionBundle\Attribute\PrecisionColumn;
use Tourze\DoctrineSnowflakeBundle\Traits\SnowflakeKeyAware;
use Tourze\DoctrineTimestampBundle\Traits\TimestampableAware;
use Tourze\DoctrineTrackBundle\Attribute\TrackColumn;
use Tourze\DoctrineUserBundle\Traits\BlameableAware;
use Tourze\ProductCoreBundle\Entity\Price;
use Tourze\ProductCoreBundle\Enum\PriceType;

// use Tourze\EasyAdmin\Attribute\Column\ExportColumn;
// use Tourze\EasyAdmin\Attribute\Field\SelectField;

/**
 * 这里记录的是订单的整体价格信息，包括优惠信息。
 * 优惠信息使用负数来展示
 *
 * 从电信模型的定义来看，这个表表述的都是定价段落（Pricing Section）
 */
#[ORM\Entity(repositoryClass: OrderPriceRepository::class)]
#[ORM\Table(name: 'order_contract_price', options: ['comment' => '订单价格信息'])]
class OrderPrice implements \Stringable
{
    use TimestampableAware;
    use BlameableAware;
    use SnowflakeKeyAware;
    use IpTraceableAware;

    #[Ignore]
    #[ORM\ManyToOne(targetEntity: Contract::class, cascade: ['persist'], inversedBy: 'prices')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private ?Contract $contract = null;

    #[TrackColumn]
    #[Groups(groups: ['restful_read', 'admin_curd'])]
    #[ORM\Column(type: Types::STRING, length: 1000, options: ['comment' => '名目'])]
    #[Assert\NotBlank]
    #[Assert\Length(max: 1000)]
    private string $name = '';

    #[TrackColumn]
    // #[SelectField(targetEntity: CurrencyManager::class)]
    #[Groups(groups: ['restful_read', 'admin_curd'])]
    #[ORM\Column(type: Types::STRING, length: 10, options: ['default' => 'CNY', 'comment' => '币种'])]
    #[Assert\NotBlank]
    #[Assert\Length(max: 10)]
    private string $currency = 'CNY';

    #[PrecisionColumn]
    #[TrackColumn]
    #[Groups(groups: ['restful_read', 'admin_curd'])]
    #[ORM\Column(type: Types::DECIMAL, precision: 20, scale: 2, options: ['comment' => '金额(小结)'])]
    #[Assert\NotNull]
    #[Assert\Length(max: 20)]
    private ?string $money = '0';

    #[PrecisionColumn]
    #[TrackColumn]
    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2, nullable: true, options: ['comment' => '税费(小结)'])]
    #[Assert\Length(max: 10)]
    private ?string $tax = null;

    #[Groups(groups: ['admin_curd'])]
    #[TrackColumn]
    #[ORM\Column(type: Types::STRING, length: 1000, nullable: true, options: ['comment' => '备注'])]
    #[Assert\Length(max: 1000)]
    private ?string $remark = null;

    /**
     * @var OrderProduct|null 关联的商品信息
     */
    #[Ignore]
    #[ORM\ManyToOne(targetEntity: OrderProduct::class, inversedBy: 'prices')]
    #[ORM\JoinColumn(onDelete: 'CASCADE')]
    private ?OrderProduct $product = null;

    #[TrackColumn]
    #[Groups(groups: ['restful_read'])]
    #[ORM\Column(type: Types::BOOLEAN, nullable: true, options: ['comment' => '是否已支付'])]
    #[Assert\Type(type: 'bool')]
    private ?bool $paid = false;

    #[TrackColumn]
    #[ORM\Column(type: Types::BOOLEAN, nullable: true, options: ['comment' => '是否可退款', 'default' => 1])]
    #[Assert\Type(type: 'bool')]
    private ?bool $canRefund = true;

    #[TrackColumn]
    #[Groups(groups: ['restful_read'])]
    #[ORM\Column(type: Types::BOOLEAN, nullable: true, options: ['comment' => '是否已退款'])]
    #[Assert\Type(type: 'bool')]
    private ?bool $refund = false;

    #[TrackColumn]
    #[ORM\Column(type: Types::STRING, length: 100, nullable: true, enumType: PriceType::class, options: ['comment' => '类型'])]
    #[Assert\Choice(choices: ['sale', 'cost', 'compete', 'freight', 'marketing', 'original_price'], message: '选择一个有效的价格类型')]
    #[Assert\NotNull]
    private PriceType $type;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(onDelete: 'SET NULL')]
    private ?Price $skuPrice = null;

    #[Groups(groups: ['restful_read'])]
    #[ORM\Column(
        type: Types::DECIMAL,
        precision: 20,
        scale: 2,
        nullable: true,
        options: ['comment' => '单价']
    )]
    #[Assert\PositiveOrZero]
    #[Assert\Length(max: 20)]
    private ?string $unitPrice = '0';

    public function __toString(): string
    {
        if (null === $this->getId() || '' === $this->getId()) {
            return '';
        }

        $money = $this->getMoney() ?? '0';
        $tax = $this->getTax() ?? '0';
        assert(is_numeric($money), 'Money must be a numeric string');
        assert(is_numeric($tax), 'Tax must be a numeric string');
        $total = bcadd($money, $tax, 2);

        return sprintf('%s: %s %s', $this->getName(), $total, $this->getCurrency());
    }

    /**
     * 不含税价格
     */
    public function getDisplayPrice(): string
    {
        return sprintf('%s %s', $this->getMoney() ?? '0', $this->getCurrency());
    }

    public function getCurrency(): string
    {
        return $this->currency;
    }

    public function setCurrency(string $currency): void
    {
        $this->currency = $currency;
    }

    public function getMoney(): ?string
    {
        return $this->money;
    }

    public function setMoney(?string $money): void
    {
        $this->money = $money;
    }

    public function getTax(): ?string
    {
        return $this->tax;
    }

    public function setTax(?string $tax): void
    {
        $this->tax = $tax;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function getContract(): ?Contract
    {
        return $this->contract;
    }

    public function setContract(?Contract $contract): void
    {
        $this->contract = $contract;
    }

    public function getRemark(): ?string
    {
        return $this->remark;
    }

    public function setRemark(?string $remark): void
    {
        $this->remark = $remark;
    }

    public function getProduct(): ?OrderProduct
    {
        return $this->product;
    }

    public function setProduct(?OrderProduct $product): void
    {
        $this->product = $product;
    }

    public function setPaid(?bool $paid): void
    {
        $this->paid = $paid;
    }

    public function setRefund(?bool $refund): void
    {
        $this->refund = $refund;
    }

    public function isCanRefund(): ?bool
    {
        return $this->canRefund;
    }

    public function setCanRefund(?bool $canRefund): void
    {
        $this->canRefund = $canRefund;
    }

    public function getType(): PriceType
    {
        return $this->type;
    }

    public function setType(PriceType $type): void
    {
        $this->type = $type;
    }

    public function getSkuPrice(): ?Price
    {
        return $this->skuPrice;
    }

    public function setSkuPrice(?Price $skuPrice): void
    {
        $this->skuPrice = $skuPrice;
    }

    /**
     * @return array<string, mixed>
     */
    public function getListArray(): array
    {
        return [
            'name' => $this->getName(),
            'money' => $this->getMoney(),
            'currency' => $this->getCurrency(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function retrieveCheckoutArray(): array
    {
        return [
            'id' => $this->getId(),
            'name' => $this->getName(),
            'currency' => $this->getCurrency(),
            'money' => $this->getMoney(),
            'paid' => $this->isPaid(),
            'refund' => $this->isRefund(),
            'unitPrice' => $this->getUnitPrice(),
            'displayPrice' => $this->getDisplayPrice(),
            'displayTaxPrice' => $this->getDisplayTaxPrice(),
        ];
    }

    public function isPaid(): ?bool
    {
        return $this->paid;
    }

    public function isRefund(): ?bool
    {
        return $this->refund;
    }

    public function getUnitPrice(): ?string
    {
        return $this->unitPrice;
    }

    public function setUnitPrice(?string $unitPrice): void
    {
        $this->unitPrice = $unitPrice;
    }

    /**
     * 含税价格
     */
    public function getDisplayTaxPrice(): string
    {
        $money = $this->getMoney() ?? '0';
        $tax = $this->getTax() ?? '0';
        assert(is_numeric($money), 'Money must be a numeric string');
        assert(is_numeric($tax), 'Tax must be a numeric string');

        // TODO: Uncomment when CurrencyManager is available
        // return Kernel::container()->get(CurrencyManager::class)->getDisplayPrice($this->getCurrency(), bcadd($money, $tax, 2));
        return $this->getCurrency() . ' ' . bcadd($money, $tax, 2);
    }
}

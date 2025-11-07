<?php

namespace OrderCoreBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use OrderCoreBundle\Enum\OrderState;
use OrderCoreBundle\Repository\ContractRepository;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;
use Tourze\Arrayable\ApiArrayInterface;
use Tourze\Arrayable\PlainArrayInterface;
use Tourze\DoctrineIndexedBundle\Attribute\IndexColumn;
use Tourze\DoctrineIpBundle\Traits\IpTraceableAware;
use Tourze\DoctrineSnowflakeBundle\Attribute\SnowflakeColumn;
use Tourze\DoctrineTimestampBundle\Traits\TimestampableAware;
use Tourze\DoctrineTrackBundle\Attribute\TrackColumn;
use Tourze\DoctrineUserBundle\Traits\BlameableAware;
use Tourze\EnumExtra\Itemable;
use Tourze\LockServiceBundle\Model\LockEntity;

/**
 * @implements PlainArrayInterface<string, mixed>
 * @implements ApiArrayInterface<string, mixed>
 * @see https://cloud.tencent.com/developer/article/1679960
 */
#[ORM\Entity(repositoryClass: ContractRepository::class)]
#[ORM\Table(name: 'order_contract_order', options: ['comment' => '契约(订单)表'])]
class Contract implements \Stringable, Itemable, PlainArrayInterface, ApiArrayInterface, LockEntity
{
    use TimestampableAware;
    use BlameableAware;
    use IpTraceableAware;

    public const LOCK_PREFIX = 'order_contract_order_';

    #[Assert\PositiveOrZero]
    #[ORM\Version]
    #[ORM\Column(type: Types::INTEGER, nullable: true, options: ['default' => 1, 'comment' => '乐观锁版本号'])]
    private ?int $lockVersion = null;

    #[Groups(groups: ['restful_read', 'admin_curd'])]
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: Types::INTEGER, options: ['comment' => '订单ID'])]
    private int $id = 0;

    #[Assert\NotBlank]
    #[Assert\Length(max: 64)]
    #[TrackColumn]
    #[Groups(groups: ['restful_read', 'admin_curd'])]
    #[SnowflakeColumn(prefix: 'C')]
    #[ORM\Column(type: Types::STRING, length: 64, unique: true, options: ['comment' => '订单编号'])]
    private string $sn = '';

    #[Assert\Length(max: 64)]
    #[TrackColumn]
    #[Groups(groups: ['restful_read'])]
    #[ORM\Column(type: Types::STRING, length: 64, nullable: true, options: ['default' => 'default', 'comment' => '类型'])]
    private ?string $type = null;

    #[Groups(groups: ['admin_curd'])]
    #[ORM\ManyToOne(targetEntity: UserInterface::class, cascade: ['persist'])]
    #[ORM\JoinColumn(onDelete: 'SET NULL')]
    private ?UserInterface $user = null;

    #[Assert\NotNull]
    #[Assert\Choice(callback: [OrderState::class, 'cases'])]
    #[IndexColumn]
    #[TrackColumn]
    #[Groups(groups: ['restful_read', 'admin_curd'])]
    #[ORM\Column(type: Types::STRING, length: 64, enumType: OrderState::class, options: ['comment' => '状态'])]
    private OrderState $state;

    #[Assert\Length(max: 64)]
    #[TrackColumn]
    #[Groups(groups: ['restful_read', 'admin_curd'])]
    #[ORM\Column(type: Types::STRING, length: 64, nullable: true, options: ['comment' => '外部订单号'])]
    private ?string $outTradeNo = null;

    /**
     * @var Collection<int, OrderContact>
     */
    #[Groups(groups: ['restful_read', 'admin_curd'])]
    #[ORM\OneToMany(mappedBy: 'contract', targetEntity: OrderContact::class, cascade: ['persist'], orphanRemoval: true)]
    private Collection $contacts;

    /**
     * @var Collection<int, OrderProduct>
     */
    #[Groups(groups: ['restful_read', 'admin_curd'])]
    #[ORM\OneToMany(mappedBy: 'contract', targetEntity: OrderProduct::class, cascade: ['persist'], fetch: 'EXTRA_LAZY', orphanRemoval: true)]
    private Collection $products;

    /**
     * @var Collection<int, OrderPrice>
     */
    #[Groups(groups: ['admin_curd'])]
    #[ORM\OneToMany(mappedBy: 'contract', targetEntity: OrderPrice::class, cascade: ['persist'], fetch: 'EXTRA_LAZY', orphanRemoval: true)]
    private Collection $prices;

    #[Assert\Length(max: 65535)]
    #[Groups(groups: ['restful_read', 'admin_curd'])]
    #[ORM\Column(type: Types::TEXT, nullable: true, options: ['comment' => '用户备注'])]
    private ?string $remark = null;

    /**
     * @var Collection<int, OrderLog>
     */
    #[Groups(groups: ['restful_read'])]
    #[ORM\OneToMany(mappedBy: 'contract', targetEntity: OrderLog::class)]
    private Collection $logs;

    #[Assert\DateTime]
    #[Groups(groups: ['restful_read', 'admin_curd'])]
    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true, options: ['comment' => '完成时间'])]
    private ?\DateTimeInterface $finishTime = null;

    #[Assert\DateTime]
    #[Groups(groups: ['restful_read', 'admin_curd'])]
    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true, options: ['comment' => '取消时间'])]
    private ?\DateTimeInterface $cancelTime = null;

    #[Assert\Length(max: 65535)]
    #[Groups(groups: ['restful_read', 'admin_curd'])]
    #[ORM\Column(type: Types::TEXT, nullable: true, options: ['comment' => '取消原因'])]
    private ?string $cancelReason = null;

    #[Assert\DateTime]
    #[Groups(groups: ['restful_read', 'admin_curd'])]
    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true, options: ['comment' => '未支付自动关闭时间'])]
    private ?\DateTimeInterface $autoCancelTime = null;

    #[Assert\DateTime]
    #[Groups(groups: ['restful_read', 'admin_curd'])]
    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true, options: ['comment' => '开始收货时间'])]
    private ?\DateTimeInterface $startReceiveTime = null;

    #[Assert\DateTime]
    #[Groups(groups: ['restful_read', 'admin_curd'])]
    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true, options: ['comment' => '过期收货时间'])]
    private ?\DateTimeInterface $expireReceiveTime = null;

    #[Assert\DateTime]
    #[Groups(groups: ['restful_read', 'admin_curd'])]
    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true, options: ['comment' => '发货时间'])]
    private ?\DateTimeInterface $shippedTime = null;

    /**
     * 支付订单信息
     * 注意：这是OneToOne关联的反向端，不能用于数据库查询
     * @internal 此字段仅用于对象关联，不支持在findBy/findOneBy查询中使用
     */
    #[Assert\Valid]
    #[Groups(groups: ['restful_read', 'admin_curd'])]
    #[ORM\OneToOne(targetEntity: PayOrder::class, mappedBy: 'contract')]
    private ?PayOrder $payOrder = null;

    #[Assert\PositiveOrZero]
    #[Groups(groups: ['restful_read', 'admin_curd'])]
    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2, nullable: true, options: ['comment' => '订单总金额（实际需要支付的价格）', 'default' => null])]
    private ?string $totalAmount = null;

    public function __construct()
    {
        $this->contacts = new ArrayCollection();
        $this->prices = new ArrayCollection();
        $this->products = new ArrayCollection();
        $this->logs = new ArrayCollection();
    }

    public function __toString(): string
    {
        try {
            if (null === $this->getId()) {
                return '';
            }

            return $this->getSn();
        } catch (\Error) {
            // id 属性未初始化时返回空字符串
            return '';
        }
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getSn(): string
    {
        return $this->sn;
    }

    public function setSn(string $sn): void
    {
        $this->sn = $sn;
    }

    public function getLockVersion(): ?int
    {
        return $this->lockVersion;
    }

    public function setLockVersion(?int $lockVersion): void
    {
        $this->lockVersion = $lockVersion;
    }

    public function getUser(): ?UserInterface
    {
        return $this->user;
    }

    public function setUser(?UserInterface $user): void
    {
        $this->user = $user;
    }

    /**
     * @return Collection<int, OrderContact>
     */
    public function getContacts(): Collection
    {
        return $this->contacts;
    }

    /**
     * @return Collection<int, OrderProduct>
     */
    public function getProducts(): Collection
    {
        return $this->products;
    }

    /**
     * @return Collection<int, OrderPrice>
     */
    public function getPrices(): Collection
    {
        return $this->prices;
    }

    /**
     * @param ArrayCollection<int, OrderPrice> $prices
     */
    public function setPrices(ArrayCollection $prices): void
    {
        $this->prices = $prices;
    }

    public function getOutTradeNo(): ?string
    {
        return $this->outTradeNo;
    }

    public function setOutTradeNo(?string $outTradeNo): void
    {
        $this->outTradeNo = $outTradeNo;
    }

    public function removeContact(OrderContact $contact): void
    {
        if ($this->contacts->removeElement($contact)) {
            if ($contact->getContract() === $this) {
                $contact->setContract(null);
            }
        }
    }

    /**
     * 根据收货地址信息创建联系人
     *
     * @param array<string, mixed> $deliveryAddressData 收货地址数据
     */
    public function addContactByDeliveryData(array $deliveryAddressData): void
    {
        $contact = new OrderContact();

        $this->setContactDeliveryAddressId($contact, $deliveryAddressData);
        $this->setContactBasicInfo($contact, $deliveryAddressData);
        $this->setContactLocationInfo($contact, $deliveryAddressData);
        $this->setContactPersonalInfo($contact, $deliveryAddressData);

        $this->addContact($contact);
    }

    /**
     * 设置联系人收货地址ID
     *
     * @param array<string, mixed> $deliveryAddressData
     */
    private function setContactDeliveryAddressId(OrderContact $contact, array $deliveryAddressData): void
    {
        if (isset($deliveryAddressData['id']) && is_numeric($deliveryAddressData['id'])) {
            $contact->setDeliveryAddressId((int) $deliveryAddressData['id']);
        }
    }

    /**
     * 设置联系人基础信息
     *
     * @param array<string, mixed> $deliveryAddressData
     */
    private function setContactBasicInfo(OrderContact $contact, array $deliveryAddressData): void
    {
        $consignee = $deliveryAddressData['consignee'] ?? '';
        $contact->setRealname(is_string($consignee) ? $consignee : '');
    }

    /**
     * 设置联系人地址信息
     *
     * @param array<string, mixed> $deliveryAddressData
     */
    private function setContactLocationInfo(OrderContact $contact, array $deliveryAddressData): void
    {
        $province = $deliveryAddressData['province'] ?? null;
        $contact->setProvinceName(is_string($province) ? $province : null);

        $city = $deliveryAddressData['city'] ?? null;
        $contact->setCityName(is_string($city) ? $city : null);

        $district = $deliveryAddressData['district'] ?? null;
        $contact->setAreaName(is_string($district) ? $district : null);

        $this->setContactFullAddress($contact, $province, $city, $district, $deliveryAddressData['addressLine'] ?? '');
    }

    /**
     * 设置联系人完整地址
     */
    private function setContactFullAddress(OrderContact $contact, mixed $province, mixed $city, mixed $district, mixed $addressLine): void
    {
        $provinceStr = is_string($province) ? $province : '';
        $cityStr = is_string($city) ? $city : '';
        $districtStr = is_string($district) ? $district : '';
        $addressLineStr = is_string($addressLine) ? $addressLine : '';
        $contact->setAddress($provinceStr . $cityStr . $districtStr . $addressLineStr);
    }

    /**
     * 设置联系人个人信息
     *
     * @param array<string, mixed> $deliveryAddressData
     */
    private function setContactPersonalInfo(OrderContact $contact, array $deliveryAddressData): void
    {
        $mobile = $deliveryAddressData['mobile'] ?? null;
        $contact->setMobile(is_string($mobile) ? $mobile : null);

        $idCard = $deliveryAddressData['idCard'] ?? null;
        $contact->setIdCard(is_string($idCard) ? $idCard : null);
    }

    public function addContact(OrderContact $contact): void
    {
        $this->addToCollection($this->contacts, $contact, function (OrderContact $item): void {
            $item->setContract($this);
        });
    }

    public function addPrice(OrderPrice $price): void
    {
        $this->addToCollection($this->prices, $price, function (OrderPrice $item): void {
            $item->setContract($this);
        });
    }

    public function removePrice(OrderPrice $price): void
    {
        $this->removeFromCollection($this->prices, $price, function (OrderPrice $item): void {
            if ($item->getContract() === $this) {
                $item->setContract(null);
            }
        });
    }

    public function addProduct(OrderProduct $product): void
    {
        $this->addToCollection($this->products, $product, function (OrderProduct $item): void {
            $item->setContract($this);
        });
    }

    /**
     * @template T
     * @param Collection<int, T> $collection
     * @param T $item
     */
    private function addToCollection(Collection $collection, mixed $item, callable $setter): void
    {
        if (!$collection->contains($item)) {
            $collection->add($item);
            $setter($item);
        }
    }

    /**
     * @template T
     * @param Collection<int, T> $collection
     * @param T $item
     */
    private function removeFromCollection(Collection $collection, mixed $item, callable $remover): void
    {
        if ($collection->removeElement($item)) {
            $remover($item);
        }
    }

    public function removeProduct(OrderProduct $product): void
    {
        $this->removeFromCollection($this->products, $product, function (OrderProduct $item): void {
            if ($item->getContract() === $this) {
                $item->setContract(null);
            }
        });
    }

    public function getState(): OrderState
    {
        return $this->state;
    }

    /**
     * @param array<string, mixed> $context
     */
    public function setState(OrderState $state, array $context = []): void
    {
        $this->state = $state;
    }

    /**
     * @return Collection<int, OrderLog>
     */
    public function getLogs(): Collection
    {
        return $this->logs;
    }

    public function addLog(OrderLog $log): void
    {
        $this->addToCollection($this->logs, $log, function (OrderLog $item): void {
            $item->setContract($this);
        });
    }

    public function removeLog(OrderLog $log): void
    {
        $this->removeFromCollection($this->logs, $log, function (OrderLog $item): void {
            if ($item->getContract() === $this) {
                $item->setContract(null);
            }
        });
    }

    public function getFinishTime(): ?\DateTimeInterface
    {
        return $this->finishTime;
    }

    public function setFinishTime(?\DateTimeInterface $finishTime): void
    {
        $this->finishTime = $finishTime;
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

    public function getAutoCancelTime(): ?\DateTimeInterface
    {
        return $this->autoCancelTime;
    }

    public function setAutoCancelTime(?\DateTimeInterface $autoCancelTime): void
    {
        $this->autoCancelTime = $autoCancelTime;
    }

    public function getStartReceiveTime(): ?\DateTimeInterface
    {
        return $this->startReceiveTime;
    }

    public function setStartReceiveTime(?\DateTimeInterface $startReceiveTime): void
    {
        $this->startReceiveTime = $startReceiveTime;
    }

    public function getExpireReceiveTime(): ?\DateTimeInterface
    {
        return $this->expireReceiveTime;
    }

    public function setExpireReceiveTime(?\DateTimeInterface $expireReceiveTime): void
    {
        $this->expireReceiveTime = $expireReceiveTime;
    }

    public function getShippedTime(): ?\DateTimeInterface
    {
        return $this->shippedTime;
    }

    public function setShippedTime(?\DateTimeInterface $shippedTime): void
    {
        $this->shippedTime = $shippedTime;
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
            'sn' => $this->getSn(),
            'remark' => $this->getRemark(),
            'state' => $this->getState(),
            'stateLabel' => $this->getState()->getLabel(),
            'totalAmount' => $this->getTotalAmount(),
            'createTime' => $this->getCreateTime()?->format('Y-m-d H:i:s'),
            'updateTime' => $this->getUpdateTime()?->format('Y-m-d H:i:s'),
            'payTime' => $this->getPayTime()?->format('Y-m-d H:i:s'),
            'shippedTime' => $this->getShippedTime()?->format('Y-m-d H:i:s'),
            'createUser' => null,
            'updateUser' => null,
        ];
    }

    public function getRemark(): ?string
    {
        return $this->remark;
    }

    public function setRemark(?string $remark): void
    {
        $this->remark = $remark;
    }

    /**
     * @return array<string, mixed>
     */
    public function retrieveCheckoutArray(): array
    {
        // 数据展示功能已移至Helper服务，请直接使用Helper服务
        return [
            'id' => $this->getId(),
        ];
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(?string $type): void
    {
        $this->type = $type;
    }

    public function retrieveLockResource(): string
    {
        return self::LOCK_PREFIX . $this->getId();
    }

    /**
     * @return array<string, mixed>
     */
    public function toSelectItem(): array
    {
        return [
            'label' => $this->getSn(),
            'text' => $this->getSn(),
            'value' => $this->getId(),
        ];
    }

    public function getPayOrder(): ?PayOrder
    {
        return $this->payOrder;
    }

    public function setPayOrder(?PayOrder $payOrder): void
    {
        // 处理旧的关联（如果存在）
        if (null === $payOrder && null !== $this->payOrder) {
            $this->payOrder->setContract(null);
        }

        // 设置新的关联
        $this->payOrder = $payOrder;

        // 确保双向关联的一致性（手动管理）
        if (null !== $payOrder && $payOrder->getContract() !== $this) {
            $payOrder->setContract($this);
        }
    }

    /**
     * 获取支付时间（从PayOrder中获取）
     */
    public function getPayTime(): ?\DateTimeInterface
    {
        return $this->payOrder?->getPayTime();
    }

    public function getTotalAmount(): ?string
    {
        return $this->totalAmount;
    }

    public function setTotalAmount(?string $totalAmount): void
    {
        $this->totalAmount = $totalAmount;
    }
}

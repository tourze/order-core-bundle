<?php

namespace OrderCoreBundle\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use OrderCoreBundle\Enum\CardType;
use OrderCoreBundle\Repository\OrderContactRepository;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Serializer\Attribute\Ignore;
use Symfony\Component\Validator\Constraints as Assert;
use Tourze\Arrayable\ApiArrayInterface;
use Tourze\DoctrineIpBundle\Traits\IpTraceableAware;
use Tourze\DoctrineSnowflakeBundle\Traits\SnowflakeKeyAware;
use Tourze\DoctrineTimestampBundle\Traits\TimestampableAware;
use Tourze\DoctrineUserBundle\Traits\BlameableAware;
use Tourze\GBT2261\Gender;

/**
 * @implements ApiArrayInterface<string, mixed>
 */
#[ORM\Entity(repositoryClass: OrderContactRepository::class)]
#[ORM\Table(name: 'order_contract_contact', options: ['comment' => '订单联系人'])]
class OrderContact implements \Stringable, ApiArrayInterface
{
    use TimestampableAware;
    use BlameableAware;
    use SnowflakeKeyAware;
    use IpTraceableAware;

    #[Ignore]
    #[ORM\ManyToOne(targetEntity: Contract::class, cascade: ['persist'], inversedBy: 'contacts')]
    #[ORM\JoinColumn(onDelete: 'SET NULL')]
    private ?Contract $contract = null;

    #[Groups(groups: ['restful_read'])]
    #[Assert\Type(type: 'integer')]
    #[ORM\Column(type: Types::BIGINT, nullable: true, options: ['comment' => '收货地址ID'])]
    private ?int $deliveryAddressId = null;

    #[Groups(groups: ['restful_read', 'admin_curd'])]
    #[ORM\Column(type: Types::STRING, length: 64, options: ['comment' => '姓名'])]
    #[Assert\NotBlank]
    #[Assert\Length(max: 64)]
    private ?string $realname = null;

    #[Groups(groups: ['restful_read', 'admin_curd'])]
    #[ORM\Column(type: Types::STRING, length: 64, nullable: true, options: ['comment' => '手机号码'])]
    #[Assert\Length(max: 64)]
    private ?string $mobile = null;

    #[Groups(groups: ['restful_read'])]
    #[ORM\Column(type: Types::STRING, length: 20, nullable: true, enumType: CardType::class, options: ['comment' => '证件类型'])]
    #[Assert\Choice(choices: ['id-card'], message: '选择一个有效的证件类型')]
    #[Assert\NotNull]
    private CardType $cardType;

    #[Groups(groups: ['restful_read'])]
    #[ORM\Column(type: Types::STRING, length: 100, nullable: true, options: ['comment' => '证件号'])]
    #[Assert\Length(max: 100)]
    private ?string $idCard = null;

    #[Groups(groups: ['restful_read', 'admin_curd'])]
    #[ORM\Column(type: Types::STRING, length: 255, nullable: true, options: ['comment' => '地址'])]
    #[Assert\Length(max: 255)]
    private ?string $address = null;

    #[Groups(groups: ['restful_read'])]
    #[ORM\Column(type: Types::STRING, length: 160, nullable: true, options: ['comment' => '邮箱地址'])]
    #[Assert\Email]
    #[Assert\Length(max: 160)]
    private ?string $email = null;

    #[Groups(groups: ['restful_read'])]
    #[ORM\Column(length: 100, nullable: true, options: ['comment' => '省份名称'])]
    #[Assert\Length(max: 100)]
    private ?string $provinceName = null;

    #[Groups(groups: ['restful_read'])]
    #[ORM\Column(length: 100, nullable: true, options: ['comment' => '城市名称'])]
    #[Assert\Length(max: 100)]
    private ?string $cityName = null;

    #[Groups(groups: ['restful_read'])]
    #[ORM\Column(length: 100, nullable: true, options: ['comment' => '地区名称'])]
    #[Assert\Length(max: 100)]
    private ?string $areaName = null;

    #[ORM\Column(type: Types::STRING, length: 64, nullable: true, options: ['comment' => '姓名（别名）'])]
    #[Assert\Length(max: 64)]
    private ?string $name = null;

    #[ORM\Column(type: Types::STRING, length: 64, nullable: true, options: ['comment' => '电话号码（别名）'])]
    #[Assert\Length(max: 64)]
    private ?string $phone = null;

    #[ORM\Column(type: Types::STRING, length: 100, nullable: true, options: ['comment' => '职位'])]
    #[Assert\Length(max: 100)]
    private ?string $position = null;

    #[ORM\Column(type: Types::STRING, length: 100, nullable: true, options: ['comment' => '部门'])]
    #[Assert\Length(max: 100)]
    private ?string $department = null;

    #[ORM\Column(type: Types::STRING, length: 50, nullable: true, options: ['comment' => '联系人类型'])]
    #[Assert\Length(max: 50)]
    private ?string $contactType = null;

    #[ORM\Column(type: Types::BOOLEAN, nullable: true, options: ['comment' => '是否激活', 'default' => true])]
    #[Assert\Type(type: 'bool')]
    private ?bool $isActive = true;

    #[ORM\Column(type: Types::STRING, length: 20, nullable: true, options: ['comment' => '省代码'])]
    #[Assert\Length(max: 20)]
    private ?string $provinceCode = null;

    #[ORM\Column(type: Types::STRING, length: 20, nullable: true, options: ['comment' => '市代码'])]
    #[Assert\Length(max: 20)]
    private ?string $cityCode = null;

    #[ORM\Column(type: Types::STRING, length: 20, nullable: true, options: ['comment' => '区/县代码'])]
    #[Assert\Length(max: 20)]
    private ?string $districtCode = null;

    #[Groups(groups: ['restful_read'])]
    #[ORM\Column(type: Types::INTEGER, nullable: true, enumType: Gender::class, options: ['comment' => '性别（0:未知 1:男 2:女 9:未说明）'])]
    #[Assert\Choice(callback: [Gender::class, 'cases'])]
    private ?Gender $gender = null;

    /**
     * @return string|null
     */
    public function getProvinceCode(): ?string
    {
        return $this->provinceCode;
    }

    /**
     * @param string|null $provinceCode
     */
    public function setProvinceCode(?string $provinceCode): void
    {
        $this->provinceCode = $provinceCode;
    }

    /**
     * @return string|null
     */
    public function getCityCode(): ?string
    {
        return $this->cityCode;
    }

    /**
     * @param string|null $cityCode
     */
    public function setCityCode(?string $cityCode): void
    {
        $this->cityCode = $cityCode;
    }

    /**
     * @return string|null
     */
    public function getDistrictCode(): ?string
    {
        return $this->districtCode;
    }

    /**
     * @param string|null $districtCode
     */
    public function setDistrictCode(?string $districtCode): void
    {
        $this->districtCode = $districtCode;
    }

    public function __toString(): string
    {
        if (null === $this->getId() || '' === $this->getId()) {
            return '';
        }

        return sprintf('%s %s', $this->getRealname(), $this->getMobile());
    }

    public function getRealname(): ?string
    {
        return $this->realname;
    }

    public function setRealname(string $realname): void
    {
        $this->realname = $realname;
    }

    public function getMobile(): ?string
    {
        return $this->mobile;
    }

    public function setMobile(?string $mobile): void
    {
        $this->mobile = $mobile;
    }

    public function getContract(): ?Contract
    {
        return $this->contract;
    }

    public function setContract(?Contract $contract): void
    {
        $this->contract = $contract;
    }

    public function getCardType(): CardType
    {
        return $this->cardType;
    }

    public function setCardType(CardType $cardType): void
    {
        $this->cardType = $cardType;
    }

    public function getIdCard(): ?string
    {
        return $this->idCard;
    }

    public function setIdCard(?string $idCard): void
    {
        $this->idCard = $idCard;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(?string $email): void
    {
        $this->email = $email;
    }

    public function getFullAddress(): string
    {
        return trim("{$this->getProvinceName()}{$this->getCityName()}{$this->getAreaName()} {$this->getAddress()}");
    }

    public function getProvinceName(): ?string
    {
        return $this->provinceName;
    }

    public function setProvinceName(?string $provinceName): void
    {
        $this->provinceName = $provinceName;
    }

    public function getCityName(): ?string
    {
        return $this->cityName;
    }

    public function setCityName(?string $cityName): void
    {
        $this->cityName = $cityName;
    }

    public function getAreaName(): ?string
    {
        return $this->areaName;
    }

    public function setAreaName(?string $areaName): void
    {
        $this->areaName = $areaName;
    }

    public function getAddress(): ?string
    {
        return $this->address;
    }

    public function setAddress(?string $address): void
    {
        $this->address = $address;
    }

    /**
     * @return array<string, mixed>
     */
    public function retrieveCheckoutArray(): array
    {
        return [
            'id' => $this->getId(),
            'deliveryAddressId' => $this->getDeliveryAddressId(),
            'realname' => $this->getRealname(),
            'mobile' => $this->getMobile(),
            'address' => $this->getAddress(),
        ];
    }

    public function getDeliveryAddressId(): ?int
    {
        return $this->deliveryAddressId;
    }

    public function setDeliveryAddressId(?int $deliveryAddressId): void
    {
        $this->deliveryAddressId = $deliveryAddressId;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): void
    {
        $this->name = $name;
    }

    public function getPhone(): ?string
    {
        return $this->phone;
    }

    public function setPhone(?string $phone): void
    {
        $this->phone = $phone;
    }

    public function getPosition(): ?string
    {
        return $this->position;
    }

    public function setPosition(?string $position): void
    {
        $this->position = $position;
    }

    public function getDepartment(): ?string
    {
        return $this->department;
    }

    public function setDepartment(?string $department): void
    {
        $this->department = $department;
    }

    public function getContactType(): ?string
    {
        return $this->contactType;
    }

    public function setContactType(?string $contactType): void
    {
        $this->contactType = $contactType;
    }

    public function getIsActive(): ?bool
    {
        return $this->isActive;
    }

    public function setIsActive(?bool $isActive): void
    {
        $this->isActive = $isActive;
    }

    /**
     * @return Gender|null
     */
    public function getGender(): ?Gender
    {
        return $this->gender;
    }

    /**
     * @param Gender|null $gender
     */
    public function setGender(?Gender $gender): void
    {
        $this->gender = $gender;
    }

    /**
     * @return array<string, mixed>
     */
    public function retrieveApiArray(): array
    {
        return [
            'id' => $this->getId(),
            'areaName' => $this->getAreaName(),
            'address' => $this->getAddress(),
            'deliveryAddressId' => $this->getDeliveryAddressId(),
            'email' => $this->getEmail(),
            'idCard' => $this->getIdCard(),
            'mobile' => $this->getMobile(),
            'realname' => $this->getRealname(),
            'cityName' => $this->getCityName(),
            'provinceName' => $this->getProvinceName(),
            'gender' => $this->getGender()?->value,
            'genderLabel' => $this->getGender()?->getLabel(),
        ];
    }
}

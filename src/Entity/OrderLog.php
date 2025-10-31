<?php

namespace OrderCoreBundle\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use OrderCoreBundle\Enum\OrderState;
use OrderCoreBundle\Repository\OrderLogRepository;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Serializer\Attribute\Ignore;
use Symfony\Component\Validator\Constraints as Assert;
use Tourze\DoctrineIpBundle\Traits\CreatedFromIpAware;
use Tourze\DoctrineSnowflakeBundle\Traits\SnowflakeKeyAware;
use Tourze\DoctrineTimestampBundle\Traits\CreateTimeAware;
use Tourze\DoctrineUserBundle\Traits\BlameableAware;

// use DoctrineEnhanceBundle\Traits\RemarkableAware; // Trait not found

#[ORM\Entity(repositoryClass: OrderLogRepository::class, readOnly: true)]
#[ORM\Table(name: 'order_log', options: ['comment' => '订单轨迹'])]
class OrderLog implements \Stringable
{
    use CreateTimeAware;
    use BlameableAware;
    use SnowflakeKeyAware;
    use CreatedFromIpAware;
    // use RemarkableAware; // Trait not found

    #[Ignore]
    #[ORM\ManyToOne(targetEntity: Contract::class, cascade: ['persist'], inversedBy: 'logs')]
    #[ORM\JoinColumn(onDelete: 'SET NULL')]
    private ?Contract $contract = null;

    #[Groups(groups: ['restful_read'])]
    #[ORM\Column(type: Types::STRING, length: 64, enumType: OrderState::class, options: ['comment' => '状态'])]
    #[Assert\Choice(choices: ['init', 'canceled', 'paying', 'paid', 'part-shipped', 'shipped', 'received', 'expired', 'aftersales-ing', 'aftersales-success', 'aftersales-failed', 'auditing', 'accept', 'reject', 'exception'], message: '选择一个有效的订单状态')]
    #[Assert\NotNull]
    private ?OrderState $currentState = null;

    #[ORM\Column(type: Types::STRING, length: 120, nullable: true, options: ['comment' => '订单号'])]
    #[Assert\Length(max: 120)]
    private ?string $orderSn = null;

    #[ORM\Column(type: Types::STRING, length: 100, nullable: true, options: ['comment' => '操作动作'])]
    #[Assert\Length(max: 100)]
    private ?string $action = null;

    #[ORM\Column(type: Types::TEXT, nullable: true, options: ['comment' => '描述信息'])]
    #[Assert\Length(max: 65535)]
    private ?string $description = null;

    #[ORM\Column(type: Types::STRING, length: 20, nullable: true, options: ['comment' => '日志级别'])]
    #[Assert\Length(max: 20)]
    private ?string $level = null;

    /**
     * @var array<string, mixed>|null 日志上下文信息，包含各种类型的数据
     */
    #[ORM\Column(type: Types::JSON, nullable: true, options: ['comment' => '上下文信息'])]
    #[Assert\Type(type: 'array')]
    private ?array $context = null;

    #[ORM\Column(type: Types::STRING, length: 45, nullable: true, options: ['comment' => 'IP地址'])]
    #[Assert\Length(max: 45)]
    private ?string $ipAddress = null;

    #[ORM\Column(type: Types::STRING, length: 500, nullable: true, options: ['comment' => '用户代理'])]
    #[Assert\Length(max: 500)]
    private ?string $userAgent = null;

    public function __toString(): string
    {
        return sprintf('%s - %s', $this->orderSn ?? '', $this->currentState?->getLabel() ?? '');
    }

    public function getContract(): ?Contract
    {
        return $this->contract;
    }

    public function setContract(?Contract $contract): void
    {
        $this->contract = $contract;
    }

    public function getCurrentState(): ?OrderState
    {
        return $this->currentState;
    }

    public function setCurrentState(OrderState $currentState): void
    {
        $this->currentState = $currentState;
    }

    public function getOrderSn(): ?string
    {
        return $this->orderSn;
    }

    public function setOrderSn(?string $orderSn): void
    {
        $this->orderSn = $orderSn;
    }

    public function getAction(): ?string
    {
        return $this->action;
    }

    public function setAction(?string $action): void
    {
        $this->action = $action;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): void
    {
        $this->description = $description;
    }

    public function getLevel(): ?string
    {
        return $this->level;
    }

    public function setLevel(?string $level): void
    {
        $this->level = $level;
    }

    /**
     * @return array<string, mixed>|null
     */
    public function getContext(): ?array
    {
        return $this->context;
    }

    /**
     * @param array<string, mixed>|null $context
     */
    public function setContext(?array $context): void
    {
        $this->context = $context;
    }

    public function getIpAddress(): ?string
    {
        return $this->ipAddress;
    }

    public function setIpAddress(?string $ipAddress): void
    {
        $this->ipAddress = $ipAddress;
    }

    public function getUserAgent(): ?string
    {
        return $this->userAgent;
    }

    public function setUserAgent(?string $userAgent): void
    {
        $this->userAgent = $userAgent;
    }
}

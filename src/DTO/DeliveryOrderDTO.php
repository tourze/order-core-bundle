<?php

namespace OrderCoreBundle\DTO;

class DeliveryOrderDTO
{
    private ?string $id = null;

    private ?string $expressCompany = null;

    private ?string $expressNumber = null;

    public function getId(): ?string
    {
        return $this->id;
    }

    public function setId(?string $id): void
    {
        $this->id = $id;
    }

    public function getExpressCompany(): ?string
    {
        return $this->expressCompany;
    }

    public function setExpressCompany(?string $expressCompany): void
    {
        $this->expressCompany = $expressCompany;
    }

    public function getExpressNumber(): ?string
    {
        return $this->expressNumber;
    }

    public function setExpressNumber(?string $expressNumber): void
    {
        $this->expressNumber = $expressNumber;
    }
}

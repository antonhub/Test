<?php

namespace App\Entity;

class Transaction
{
    private ?string $bin = null;

    private ?float $amount = null;

    private ?string $currency = null;

    public function getBin(): ?string
    {
        return $this->bin;
    }

    public function setBin(string $bin): static
    {
        $this->bin = $bin;

        return $this;
    }

    public function getAmount(): ?float
    {
        return $this->amount;
    }

    public function setAmount(float $amount): static
    {
        $this->amount = $amount;

        return $this;
    }

    public function getCurrency(): ?string
    {
        return $this->currency;
    }

    public function setCurrency(string $currency): static
    {
        $this->currency = $currency;

        return $this;
    }
}

<?php

namespace App\Entity;

use App\Repository\InvoiceRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: InvoiceRepository::class)]
class Invoice
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $InvoiceDate = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2)]
    private ?string $amountWithoutTax = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2)]
    private ?string $taxAmount = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2)]
    private ?string $totalAmount = null;

    #[ORM\ManyToOne(inversedBy: 'invoices')]
    private ?User $UserInvoice = null;

    #[ORM\ManyToOne(inversedBy: 'invoices')]
    private ?Transaction $transactionInvoice = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getInvoiceDate(): ?\DateTimeInterface
    {
        return $this->InvoiceDate;
    }

    public function setInvoiceDate(\DateTimeInterface $InvoiceDate): static
    {
        $this->InvoiceDate = $InvoiceDate;

        return $this;
    }

    public function getAmountWithoutTax(): ?string
    {
        return $this->amountWithoutTax;
    }

    public function setAmountWithoutTax(string $amountWithoutTax): static
    {
        $this->amountWithoutTax = $amountWithoutTax;

        return $this;
    }

    public function getTaxAmount(): ?string
    {
        return $this->taxAmount;
    }

    public function setTaxAmount(string $taxAmount): static
    {
        $this->taxAmount = $taxAmount;

        return $this;
    }

    public function getTotalAmount(): ?string
    {
        return $this->totalAmount;
    }

    public function setTotalAmount(string $totalAmount): static
    {
        $this->totalAmount = $totalAmount;

        return $this;
    }

    public function getUserInvoice(): ?User
    {
        return $this->UserInvoice;
    }

    public function setUserInvoice(?User $UserInvoice): static
    {
        $this->UserInvoice = $UserInvoice;

        return $this;
    }

    public function getTransactionInvoice(): ?Transaction
    {
        return $this->transactionInvoice;
    }

    public function setTransactionInvoice(?Transaction $transactionInvoice): static
    {
        $this->transactionInvoice = $transactionInvoice;

        return $this;
    }
}

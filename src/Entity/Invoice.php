<?php

namespace App\Entity;

use App\Repository\InvoiceRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use App\Entity\User;

#[ORM\Entity(repositoryClass: InvoiceRepository::class)]
class Invoice
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $invoiceDate = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2)]
    private ?float $amountWithoutTax = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2)]
    private ?float $taxAmount = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2)]
    private ?float $totalAmount = null;

    // Relation ManyToOne avec l'entité User
    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'invoices')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $userInvoice = null;

    #[ORM\ManyToOne(inversedBy: 'invoices')]
    private ?Transaction $transactionInvoice = null;

    // Getters et Setters

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getInvoiceDate(): ?\DateTimeInterface
    {
        return $this->invoiceDate;
    }

    public function setInvoiceDate(\DateTimeInterface $invoiceDate): static
    {
        $this->invoiceDate = $invoiceDate;

        return $this;
    }

    public function getAmountWithoutTax(): ?float
    {
        return $this->amountWithoutTax;
    }

    public function setAmountWithoutTax(float $amountWithoutTax): static
    {
        $this->amountWithoutTax = $amountWithoutTax;

        return $this;
    }

    public function getTaxAmount(): ?float
    {
        return $this->taxAmount;
    }

    public function setTaxAmount(float $taxAmount): static
    {
        $this->taxAmount = $taxAmount;

        return $this;
    }

    public function getTotalAmount(): ?float
    {
        return $this->totalAmount;
    }

    public function setTotalAmount(float $totalAmount): static
    {
        $this->totalAmount = $totalAmount;

        return $this;
    }

    public function getUserInvoice(): ?User
    {
        return $this->userInvoice;
    }

    public function setUserInvoice(?User $userInvoice): static
    {
        $this->userInvoice = $userInvoice;

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

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
    private ?\DateTimeInterface $InvoiceDate = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2)]
    private ?string $amountWithoutTax = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2)]
    private ?string $taxAmount = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2)]
    private ?string $totalAmount = null;

    // Relation ManyToOne avec l'entité User
    // reminder : un utilisateur peut avoir plusieurs factures, mais chaque facture est associée à un seul utilisateur
    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'invoices')] 
    #[ORM\JoinColumn(nullable: false)] // Attention : la facture doit toujours avoir un utilisateur sinon erreur
    private ?User $UserInvoice = null;

    #[ORM\ManyToOne(inversedBy: 'invoices')]
    private ?Transaction $transactionInvoice = null;

    // Getter et Setter pour l'ID de la facture
    public function getId(): ?int
    {
        return $this->id;
    }

    // Getter et Setter pour la date de la facture
    public function getInvoiceDate(): ?\DateTimeInterface
    {
        return $this->InvoiceDate;
    }

    public function setInvoiceDate(\DateTimeInterface $InvoiceDate): static
    {
        $this->InvoiceDate = $InvoiceDate;

        return $this;
    }

    // Getter et Setter pour le montant hors taxe
    public function getAmountWithoutTax(): ?string
    {
        return $this->amountWithoutTax;
    }

    public function setAmountWithoutTax(string $amountWithoutTax): static
    {
        $this->amountWithoutTax = $amountWithoutTax;

        return $this;
    }

    // Getter et Setter pour le montant de la taxe
    public function getTaxAmount(): ?string
    {
        return $this->taxAmount;
    }

    public function setTaxAmount(string $taxAmount): static
    {
        $this->taxAmount = $taxAmount;

        return $this;
    }

    // Getter et Setter pour le montant total
    public function getTotalAmount(): ?string
    {
        return $this->totalAmount;
    }

    public function setTotalAmount(string $totalAmount): static
    {
        $this->totalAmount = $totalAmount;

        return $this;
    }

    // Getter et Setter pour l'utilisateur (relation ManyToOne)
    public function getUserInvoice(): ?User
    {
        return $this->UserInvoice;
    }

    public function setUserInvoice(?User $UserInvoice): static
    {
        $this->UserInvoice = $UserInvoice;

        return $this;
    }

    // Getter et Setter pour la transaction
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

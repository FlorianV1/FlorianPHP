<?php

declare(strict_types=1);

namespace App\Enums;

use Filament\Support\Contracts\HasDescription;
use Filament\Support\Contracts\HasLabel;

/**
 * How this company charges VAT, which is what decides whether an invoice is
 * allowed to carry a VAT row at all.
 *
 * The KvK number is deliberately not part of this: it is an obligation that
 * attaches to being registered in the Handelsregister, not one of the invoice
 * fields in art. 35a Wet OB. What a missing registration really constrains is
 * the VAT identification number — and without one no VAT may be charged. So
 * the regime, not the editable `vat_rate` column, is the source of truth for
 * the rate a new invoice opens with.
 */
enum VatRegime: string implements HasDescription, HasLabel
{
    /** No Handelsregister entry and no VAT id: a payment request, not a VAT invoice. */
    case NotRegistered = 'not_registered';

    /** Registered, but exempt from charging under the kleineondernemersregeling. */
    case SmallBusinessScheme = 'small_business_scheme';

    /** The ordinary Dutch rate. */
    case Standard = 'standard';

    public function rate(): float
    {
        return match ($this) {
            self::NotRegistered, self::SmallBusinessScheme => 0.0,
            self::Standard => 21.0,
        };
    }

    public function chargesVat(): bool
    {
        return $this->rate() > 0.0;
    }

    /**
     * Whether the regime can only be claimed with a VAT identification
     * number of our own. The settings page uses this to refuse a regime the
     * stored identity cannot back up.
     */
    public function requiresVatNumber(): bool
    {
        return $this !== self::NotRegistered;
    }

    /**
     * What the document calls itself. Without a VAT registration it is not a
     * `factuur` in the VAT sense, so it goes out as a nota — the client
     * cannot reclaim VAT off it either way, and the title should say so.
     */
    public function documentTitle(): string
    {
        return match ($this) {
            self::NotRegistered => 'Nota',
            self::SmallBusinessScheme, self::Standard => 'Factuur',
        };
    }

    /**
     * The statement printed in place of a VAT row. Dutch, because the reader
     * is the client's bookkeeper or the Belastingdienst.
     */
    public function invoiceNote(): ?string
    {
        return match ($this) {
            self::NotRegistered => 'Geen btw in rekening gebracht: geen btw-plichtige ondernemer.',
            self::SmallBusinessScheme => 'Geen btw in rekening gebracht in verband met de kleineondernemersregeling.',
            self::Standard => null,
        };
    }

    public function getLabel(): string
    {
        return $this->label();
    }

    public function getDescription(): ?string
    {
        return $this->description();
    }

    public function label(): string
    {
        return match ($this) {
            self::NotRegistered => 'Not registered — nota, no VAT',
            self::SmallBusinessScheme => 'Small business scheme (KOR) — no VAT',
            self::Standard => 'Standard — 21% VAT',
        };
    }

    public function description(): string
    {
        return match ($this) {
            self::NotRegistered => 'No KvK number and no VAT id. Invoices go out as a payment request with no VAT row, and the client cannot deduct VAT.',
            self::SmallBusinessScheme => 'Registered with a VAT id but exempt from charging. A real invoice with no VAT row and the KOR statement.',
            self::Standard => 'Charge 21% and state it. Requires a VAT id on every invoice.',
        };
    }
}

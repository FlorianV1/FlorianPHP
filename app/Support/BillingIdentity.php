<?php

declare(strict_types=1);

namespace App\Support;

use App\Enums\VatRegime;
use App\Models\Settings;

/**
 * One source of truth for who is sending the invoice: the sender block, the
 * payment details, and the VAT regime. The invoice PDF used to print nothing
 * but `config('app.name')`, which is not a document anyone can pay or book.
 *
 * `kvk_number` and `vat_number` are deliberately nullable. Nothing here
 * requires them, because they only exist once there is a Handelsregister
 * entry — the PDF omits whichever line is empty rather than printing a blank
 * label. Filling them in later is a settings edit, not a migration.
 *
 * Invoices snapshot these values at draft time (see `Invoice::openDraftFor`),
 * so registering with the KvK halfway through a financial year never rewrites
 * the documents already sent.
 */
class BillingIdentity
{
    public const KEY = 'billing_identity';

    /**
     * @return array<string, string|null>
     */
    public static function defaults(): array
    {
        return [
            'legal_name' => 'Florian Geense',
            'trade_name' => null,
            'address_line' => null,
            'postal_code' => null,
            'city' => null,
            'country' => 'Nederland',
            'email' => null,
            'phone' => null,
            'website' => null,
            'iban' => null,
            'bic' => null,
            'kvk_number' => null,
            'vat_number' => null,
            'vat_regime' => VatRegime::NotRegistered->value,
            'payment_terms_days' => '14',
            'footer_note' => null,
        ];
    }

    /**
     * @return array<string, string|null>
     */
    public static function all(): array
    {
        $stored = Settings::get(self::KEY, []);

        return array_merge(
            self::defaults(),
            is_array($stored) ? array_filter($stored, fn ($value) => $value !== null && $value !== '') : [],
        );
    }

    public static function get(string $key, mixed $fallback = null): mixed
    {
        return self::all()[$key] ?? $fallback;
    }

    /**
     * The configured regime, falling back to the most conservative one: an
     * unreadable or unset value must never cause VAT to be charged under a
     * registration that does not exist.
     */
    public static function regime(): VatRegime
    {
        return VatRegime::tryFrom((string) self::get('vat_regime')) ?? VatRegime::NotRegistered;
    }

    public static function vatRate(): float
    {
        return self::regime()->rate();
    }

    public static function paymentTermsDays(): int
    {
        return max(0, (int) self::get('payment_terms_days', 14));
    }

    /**
     * The sender block as the PDF needs it: display name, the address lines
     * that are actually filled in, and the identifiers that exist. Stored on
     * the invoice as-is, so the template never reads live settings.
     *
     * @return array{
     *     name: string,
     *     trade_name: string|null,
     *     address_lines: list<string>,
     *     email: string|null,
     *     phone: string|null,
     *     website: string|null,
     *     iban: string|null,
     *     bic: string|null,
     *     kvk_number: string|null,
     *     vat_number: string|null,
     * }
     */
    public static function snapshot(): array
    {
        $identity = self::all();

        $addressLines = array_values(array_filter([
            $identity['address_line'] ?? null,
            trim(($identity['postal_code'] ?? '').' '.($identity['city'] ?? '')) ?: null,
            $identity['country'] ?? null,
        ], fn ($line) => $line !== null && $line !== ''));

        return [
            'name' => (string) ($identity['legal_name'] ?? config('app.name')),
            'trade_name' => $identity['trade_name'] ?? null,
            'address_lines' => $addressLines,
            'email' => $identity['email'] ?? null,
            'phone' => $identity['phone'] ?? null,
            'website' => $identity['website'] ?? null,
            'iban' => $identity['iban'] ?? null,
            'bic' => $identity['bic'] ?? null,
            'kvk_number' => $identity['kvk_number'] ?? null,
            'vat_number' => $identity['vat_number'] ?? null,
        ];
    }
}

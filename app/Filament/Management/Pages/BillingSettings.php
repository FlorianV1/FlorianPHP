<?php

declare(strict_types=1);

namespace App\Filament\Management\Pages;

use App\Enums\VatRegime;
use App\Models\Settings;
use App\Support\BillingIdentity;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Concerns\InteractsWithSchemas;
use Filament\Schemas\Contracts\HasSchemas;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use UnitEnum;

/**
 * Who the invoice says it is from.
 *
 * Every identifier here is optional except the name: the KvK number and the
 * btw-id only exist once there is a Handelsregister entry, and an invoice is
 * perfectly valid without the ones that do not apply to the sender. What is
 * not optional is matching the VAT regime, which is why the btw-id becomes
 * required for the two regimes that cannot be claimed without one.
 */
final class BillingSettings extends Page implements HasSchemas
{
    use InteractsWithSchemas;

    protected string $view = 'filament.management.pages.billing-settings';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedIdentification;

    protected static string|UnitEnum|null $navigationGroup = 'Billing';

    protected static ?int $navigationSort = 4;

    protected static ?string $navigationLabel = 'Billing identity';

    protected static ?string $title = 'Billing identity';

    /** @var array<string, mixed>|null */
    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill(BillingIdentity::all());
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->statePath('data')
            ->components([
                Section::make('Who is sending the invoice')
                    ->description('Printed at the top of every invoice. Copied onto each invoice as it is drafted, so changing it here never rewrites documents already sent.')
                    ->schema([
                        Grid::make(2)->schema([
                            TextInput::make('legal_name')
                                ->label('Legal name')
                                ->required()
                                ->helperText('Your own name until there is a registered company name.'),

                            TextInput::make('trade_name')
                                ->label('Trade name')
                                ->placeholder('none')
                                ->helperText('Optional. Only a registered handelsnaam belongs here.'),
                        ]),

                        TextInput::make('address_line')
                            ->label('Street and number')
                            ->columnSpanFull(),

                        Grid::make(3)->schema([
                            TextInput::make('postal_code')->label('Postal code'),
                            TextInput::make('city')->label('City'),
                            TextInput::make('country')->label('Country'),
                        ]),

                        Grid::make(3)->schema([
                            TextInput::make('email')->label('Email')->email(),
                            TextInput::make('phone')->label('Phone')->tel(),
                            TextInput::make('website')->label('Website')->url(),
                        ]),
                    ]),

                Section::make('Registration')
                    ->description('Leave both identifiers empty until they exist. The invoice omits whichever line is blank; it never prints a labelled blank.')
                    ->schema([
                        Select::make('vat_regime')
                            ->label('VAT regime')
                            ->options(VatRegime::class)
                            ->default(VatRegime::NotRegistered)
                            ->required()
                            ->live()
                            ->helperText(fn (Get $get): string => self::regimeFrom($get)->description())
                            ->columnSpanFull(),

                        Grid::make(2)->schema([
                            TextInput::make('kvk_number')
                                ->label('KvK number')
                                ->placeholder('not registered')
                                ->helperText('Optional. Belongs on an invoice only once you are in the Handelsregister.'),

                            TextInput::make('vat_number')
                                ->label('Btw-id')
                                ->placeholder('NL000000000B00')
                                // The one hard rule here: a regime that charges
                                // VAT, or claims an exemption from it, needs a
                                // number to claim it under. Saving 21% with no
                                // btw-id would print a false statement on every
                                // invoice that followed.
                                ->required(fn (Get $get): bool => self::regimeFrom($get)->requiresVatNumber())
                                ->helperText('Required for the KOR and standard regimes.'),
                        ]),
                    ]),

                Section::make('Payment')
                    ->description('Without an IBAN the invoice gives nobody a way to pay it.')
                    ->schema([
                        Grid::make(3)->schema([
                            TextInput::make('iban')->label('IBAN'),
                            TextInput::make('bic')->label('BIC')->placeholder('optional'),
                            TextInput::make('payment_terms_days')
                                ->label('Payment term (days)')
                                ->numeric()
                                ->minValue(0)
                                ->maxValue(90)
                                ->default(14),
                        ]),

                        Textarea::make('footer_note')
                            ->label('Footer note')
                            ->rows(2)
                            ->columnSpanFull(),
                    ]),
            ]);
    }

    public function save(): void
    {
        $data = $this->form->getState();

        Settings::set(BillingIdentity::KEY, array_merge(
            BillingIdentity::defaults(),
            array_filter($data, fn ($value) => $value !== null && $value !== ''),
        ));

        Notification::make()
            ->title('Billing identity saved')
            ->body('Invoices drafted from now on carry these details.')
            ->success()
            ->send();
    }

    /**
     * The regime currently selected in the form, defaulting to the one that
     * claims the least.
     *
     * The state is a `VatRegime` while it is still the field's default and a
     * plain string once the user has picked one, so both have to be read.
     */
    private static function regimeFrom(Get $get): VatRegime
    {
        $state = $get('vat_regime');

        if ($state instanceof VatRegime) {
            return $state;
        }

        return VatRegime::tryFrom((string) $state) ?? VatRegime::NotRegistered;
    }

    /**
     * @return array<Action>
     */
    protected function getHeaderActions(): array
    {
        return [
            Action::make('save')
                ->label('Save')
                ->action('save')
                ->color('primary'),
        ];
    }
}

<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CompanyResource\Pages;
use App\Filament\Resources\CompanyResource\RelationManagers;
use App\Models\Company;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Section;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class CompanyResource extends Resource
{
    protected static ?string $model = Company::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Community Information')
                    ->description('Community Information')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->placeholder('Community Name')
                            ->unique()
                            ->autocapitalize()
                            ->required()
                            ->label('Community Name')
                            ->maxLength(255),
                        RichEditor::make('description')
                            ->placeholder('Community Description')
                            ->required()
                            ->label('Community Description')
                            ->maxLength(65535),
                    ]),
                Section::make("Account Information")
                    ->description('Account Information')
                    ->schema([
                        Forms\Components\TextInput::make('account_name')
                            ->placeholder('Account Name')
                            ->required()
                            ->live()
                            ->label('Account Name')
                            ->debounce(1000)
                            ->autocapitalize()
                            ->maxLength(255)
                            ->afterStateUpdated(
                                function (callable $set, $state) {
                                    //incase the account name is set then set the account number
                                    //$account_number =  Str::random(20);
                                    $set('account_number', 12);
                                }
                            ),
                        Forms\Components\TextInput::make('account_number')
                            ->placeholder('Account Number')
                            ->required()
                            ->label('Account Number')
                            // ->disabled()
                            ->unique()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('account_balance')
                            ->prefix("UGX")
                            ->required()
                            ->disabled()
                            ->default(0)
                            ->label('Account Balance')
                            ->maxLength(255),
                    ]),

                //community leader
                Section::make("Community Leader Information")
                    ->description('Community Leader Information')
                    ->schema([
                        Forms\Components\Select::make('leader_id')
                            ->label('Community Leader')
                            ->required()
                            ->options(User::all()->pluck('name', 'id'))
                            ->searchable()
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([])
            ->filters([
                //Tables\Filters\TrashedFilter::make(),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\ForceDeleteBulkAction::make(),
                    Tables\Actions\RestoreBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCompanies::route('/'),
            'create' => Pages\CreateCompany::route('/create'),
            'view' => Pages\ViewCompany::route('/{record}'),
            'edit' => Pages\EditCompany::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }
}

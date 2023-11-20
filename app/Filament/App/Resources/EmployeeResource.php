<?php

namespace App\Filament\App\Resources;

use App\Filament\App\Resources\EmployeeResource\Pages;
use App\Filament\App\Resources\EmployeeResource\RelationManagers;
use App\Models\City;
use App\Models\Employee;
use App\Models\State;
use Filament\Facades\Filament;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Collection;

class EmployeeResource extends Resource
{
    protected static ?string $model = Employee::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
        ->schema([
            Forms\Components\Section::make('User Name')
            ->schema([
            Forms\Components\Select::make('country_id')
                ->relationship(name: 'country', titleAttribute: 'name')
                ->searchable()
                ->preload() // to load countries before select
                ->required()
                ->live()  // alow form to render when value of country field change
                ->afterStateUpdated(function (Set $set) {
                    $set('state_id', null);
                    $set('city_id', null);
                })
                ->required(),
            Forms\Components\Select::make('state_id') // to get value related to other
            ->options(fn(Get $get):  Collection => State::query()
                        ->where('country_id', $get('country_id'))
                        ->pluck('name', 'id'))
                ->searchable()
                ->preload() // to load countries before select
                ->live()
                ->afterStateUpdated(fn (Set $set) => $set('city_id', null))
                ->required(),
            Forms\Components\Select::make('city_id')
            ->options(fn(Get $get):  Collection => City::query()
                        ->where('state_id', $get('state_id'))
                        ->pluck('name', 'id'))
                ->searchable()
                ->preload() // to load countries before select
                ->required(),
            Forms\Components\Select::make('department_id')
                ->relationship(
                    name: 'department', 
                    titleAttribute: 'name',
                    modifyQueryUsing: fn(Builder $query) 
                    => $query->whereBelongsTo(Filament::getTenant()) //get department related to this team
                    )
                ->searchable()
                ->preload() // to load countries before select
                ->required(),
            ])->columns(2),
            Forms\Components\Section::make('User Name')
            ->schema([
                Forms\Components\TextInput::make('first_name')
                ->required()
                ->maxLength(255),
            Forms\Components\TextInput::make('last_name')
                ->required()
                ->maxLength(255),
            Forms\Components\TextInput::make('middle_name')
                ->required()
                ->maxLength(255),
            ])->columns(3),
            Forms\Components\Section::make('User address')
            ->schema([
                Forms\Components\TextInput::make('address')
                ->required()
                ->maxLength(255),
            Forms\Components\TextInput::make('zip_code')
                ->required()
                ->maxLength(255),
            ])->columns(2),
            Forms\Components\Section::make('Dates')
            ->schema([
                Forms\Components\DatePicker::make('date_of_birth')
                ->native(false)
                ->displayFormat('d/m/y')
                ->required(),
            Forms\Components\DatePicker::make('date_hired')
                ->native(false)
                ->displayFormat('d/m/y')
                ->required()
                // ->columnSpanFull(), // change full span for column
            ])->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                //
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->emptyStateActions([
                Tables\Actions\CreateAction::make(),
            ]);
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Section::make('Relationships')
                    ->schema([
                        TextEntry::make('country.name'),
                        TextEntry::make(
                            'state.name'
                        ),
                        TextEntry::make(
                            'city.name'
                        ),
                        TextEntry::make('department.name'),
                    ])->columns(2),
                Section::make('Name')
                    ->schema([
                        TextEntry::make('first_name'),
                        TextEntry::make(
                            'middle_name'
                        ),
                        TextEntry::make(
                            'last_name'
                        ),
                    ])->columns(3),
                Section::make('Address')
                    ->schema([
                        TextEntry::make('address'),
                        TextEntry::make(
                            'zip_code'
                        ),
                    ])->columns(2)
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
            'index' => Pages\ListEmployees::route('/'),
            'create' => Pages\CreateEmployee::route('/create'),
            'view' => Pages\ViewEmployee::route('/{record}'),
            'edit' => Pages\EditEmployee::route('/{record}/edit'),
        ];
    }    
}

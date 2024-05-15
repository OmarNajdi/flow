<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages;
use App\Filament\Resources\UserResource\RelationManagers;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Infolists\Infolist;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-o-users';

//    protected static ?string $navigationGroup = 'User';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                //
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id'),
                TextColumn::make('first_name'),
                TextColumn::make('last_name'),
                TextColumn::make('email'),
                TextColumn::make('created_at')->label('Registration Time')->dateTime(),
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
                ]),
            ]);
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Section::make('Contact Information')
                    ->schema([
                        TextEntry::make('first_name')->label('First Name'),
                        TextEntry::make('last_name')->label('Last Name'),
                        TextEntry::make('email')->label('Email'),
                        TextEntry::make('dob')->label('Date of Birth'),
                        TextEntry::make('phone')->label('Phone'),
                        TextEntry::make('whatsapp')->label('Whatsapp'),
                    ])->columns(2),
                Section::make('Personal Information')
                    ->schema([
                        TextEntry::make('gender')->label('Gender'),
                        TextEntry::make('residence')->label('Residence'),
                        TextEntry::make('description')->label('Describe Yourself'),
                        TextEntry::make('occupation')->label('Occupation'),
                    ])->columns(2),
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
            'index' => Pages\ListUsers::route('/'),
            'view'  => Pages\ViewUser::route('/{record}'),
        ];
    }

//
//    public static function canViewAny(): bool
//    {
//        return auth()->id() <= 5;
//    }
//
//    public static function canCreate(): bool
//    {
//        return false;
//    }
//
//    public static function canEdit(Model $record): bool
//    {
//        return false;
//    }
//
//    public static function canDelete(Model $record): bool
//    {
//        return false;
//    }
//
//    public static function shouldRegisterNavigation(): bool
//    {
//        return auth()->id() <= 5;
//    }

}

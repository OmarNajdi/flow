<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProgramResource\Pages;
use App\Filament\Resources\ProgramResource\RelationManagers;
use App\Models\Program;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Tables\Actions\Action;

class ProgramResource extends Resource
{
    protected static ?string $model = Program::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?int $navigationSort = 2;

    protected static ?string $navigationGroup = 'Programs';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('name')->label('Name')->required(),
                Select::make('level')->label('Level')->options([
                    'pre-incubation'   => 'Pre-Incubation',
                    'incubation'       => 'Incubation',
                    'pre-acceleration' => 'Pre-Acceleration',
                    'community'        => 'Community Development',
                ])->required(),
                DatePicker::make('open_date')->native(false)->label('Open Date')->required(),
                DatePicker::make('close_date')->native(false)->label('Close Date')->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name'),
                TextColumn::make('level')->formatStateUsing(fn(string $state): string => ucwords($state, '- ')),
                TextColumn::make('open_date'),
                TextColumn::make('close_date'),
                TextColumn::make('applications_count')->label('Applications')->counts('applications'),
            ])
            ->filters([
                //
            ])
            ->actions([
                Action::make('apply')->label('Apply')->button()->icon('heroicon-s-plus')
                    ->url(fn(Program $record): string => ApplicationResource::getUrl('create', ['program' => $record])),
                Tables\Actions\ViewAction::make()->button(),
                Tables\Actions\EditAction::make()->button()->color('gray'),
            ])
            ->recordUrl(function ($record) {
                if ($record->trashed()) {
                    return null;
                }

                return ApplicationResource::getUrl('create');
            })
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
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
            'index'  => Pages\ListPrograms::route('/'),
            'create' => Pages\CreateProgram::route('/create'),
            'view'   => Pages\ViewProgram::route('/{record}'),
            'edit'   => Pages\EditProgram::route('/{record}/edit'),
        ];
    }
}

<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProgramResource\Pages;
use App\Filament\Resources\ProgramResource\RelationManagers;
use App\Models\Program;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\Split;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Resource;
use Filament\Support\Enums\FontWeight;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Tables\Actions\Action;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Illuminate\Database\Eloquent\Model;


class ProgramResource extends Resource
{
    protected static ?string $model = Program::class;

    protected static ?string $navigationIcon = 'heroicon-o-book-open';

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
                RichEditor::make('description')->label('Description')->required()->columnSpan(2),
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

                return ApplicationResource::getUrl('create', ['program' => $record]);
            });
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Split::make([
                    Section::make([
                        TextEntry::make('description')->html()->hiddenLabel(),
                    ]),
                    Section::make([
                        TextEntry::make('name')
                            ->weight(FontWeight::Bold),
                        TextEntry::make('level')->formatStateUsing(fn(string $state): string => ucwords($state, '- ')),
                        TextEntry::make('open_date')->date(),
                        TextEntry::make('close_date')->date(),
                        Infolists\Components\Actions::make([
                            Infolists\Components\Actions\Action::make('apply')
                                ->label('Apply')
                                ->icon('heroicon-s-plus')
                                ->url(fn(Program $record): string => ApplicationResource::getUrl('create',
                                    ['program' => $record])),
                        ]),
                    ])->grow(false),
                ])->from('md')->columnSpan(2)
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

    public static function canCreate(): bool
    {
        return false;
    }

    public static function canEdit(Model $record): bool
    {
        return false;
    }

    public static function canDelete(Model $record): bool
    {
        return false;
    }
}

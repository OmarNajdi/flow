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
use Filament\Support\Enums\Alignment;
use Filament\Support\Enums\FontWeight;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Tables\Actions\Action;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\HtmlString;


class ProgramResource extends Resource
{
    protected static ?string $model = Program::class;

    protected static ?string $navigationIcon = 'heroicon-o-book-open';

    protected static ?int $navigationSort = 2;

    public static function getNavigationGroup(): ?string
    {
        return __('Programs');
    }

    public static function getNavigationLabel(): string
    {
        return __('Programs');
    }

    public static function getPluralModelLabel(): string
    {
        return __('Programs');
    }


    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('name')->label('Name')->required(),
                Select::make('level')->label('Level')->options([
                    'ideation and innovation' => 'Ideation and Innovation',
                    'pre-incubation'          => 'Pre-Incubation',
                    'incubation'              => 'Incubation',
                    'pre-acceleration'        => 'Pre-Acceleration',
                    'acceleration'            => 'Acceleration',
                    'formation'               => 'Formation and Development',
                ])->required(),
                TextInput::make('activity')->label('Activity'),
                Select::make('status')->label('Status')->options([
                    'draft'         => 'Draft',
                    'open'          => 'Open',
                    'in review'     => 'In Review',
                    'decision made' => 'Decision Made'
                ])->default('open')->required(),
                DatePicker::make('open_date')->native(false)->label('Open Date')->required(),
                DatePicker::make('close_date')->native(false)->label('Close Date')->required(),
                RichEditor::make('description')->label('Description')->required()->columnSpan(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')->translateLabel(),
                TextColumn::make('level')
                    ->formatStateUsing(fn(string $state): string => ucwords(__($state), '- '))->translateLabel(),
                TextColumn::make('activity')->translateLabel()
                    ->formatStateUsing(fn(string $state): string => __($state)),
                TextColumn::make('open_date')->date()->sortable()->translateLabel(),
                TextColumn::make('close_date')->date()->sortable()->translateLabel(),
                TextColumn::make('status')
                    ->formatStateUsing(fn(string $state): string => ucwords($state, '- '))
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'draft' => 'info',
                        'open' => 'success',
                        'in review' => 'warning',
                        'incomplete' => 'danger',
                        'decision made' => 'gray',
                        default => 'gray',
                    })
                    ->sortable()
                    ->translateLabel(),
            ])
            ->filters([
                //
            ])
            ->actions([
                Action::make('apply')->label('Apply')->button()->icon('heroicon-s-plus')
                    ->url(fn(Program $record): string => ApplicationResource::getUrl('create', ['program' => $record]))
                    ->visible(fn(Program $record): bool => in_array($record->status, ['open', 'draft'])),
                Tables\Actions\ViewAction::make()->button(),
                Tables\Actions\EditAction::make()->button()->color('gray'),
                Action::make('export')->label('Export')->button()->color('gray')->icon('heroicon-o-arrow-down-tray')
                    ->url(fn(Program $record): string => route('applications.export',
                        ['program_id' => $record->id, 'program_level' => $record->level]))
                    ->visible(fn(): bool => auth()->id() <= 6),
            ])
            ->recordUrl(function ($record) {
                if ($record->trashed()) {
                    return null;
                }

                return ProgramResource::getUrl('view', [$record]);
            })
            ->emptyStateHeading('There are no open programs at the moment, please check later.');
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Split::make([
                    Section::make([
                        TextEntry::make('description')->html()->hiddenLabel()->formatStateUsing(fn(string $state
                        ): HtmlString => new HtmlString(__($state))),
                        Infolists\Components\Actions::make([
                            Infolists\Components\Actions\Action::make('apply')
                                ->label('Apply')
                                ->size('xl')
                                ->icon('heroicon-s-plus')
                                ->url(fn(Program $record): string => ApplicationResource::getUrl('create',
                                    ['program' => $record]))
                                ->visible(fn(Program $record): bool => in_array($record->status, ['open', 'draft'])),
                        ])->fullWidth(),
                    ]),
                    Section::make([
                        TextEntry::make('name')->label('Program Name')
                            ->weight(FontWeight::Bold),
                        TextEntry::make('level')->formatStateUsing(fn(string $state): string => ucwords(__($state),
                            '- ')),
                        TextEntry::make('activity')->formatStateUsing(fn(string $state): string => __($state))
                            ->hidden(fn($state): bool => ! $state),
                        TextEntry::make('open_date')->date('l, M j, Y'),
                        TextEntry::make('close_date')->date('l, M j, Y'),
                        TextEntry::make('status')->formatStateUsing(fn(string $state): string => ucwords($state, '- ')),
                        Infolists\Components\Actions::make([
                            Infolists\Components\Actions\Action::make('apply')
                                ->label('Apply')
                                ->size('xl')
                                ->icon('heroicon-s-plus')
                                ->url(fn(Program $record): string => ApplicationResource::getUrl('create',
                                    ['program' => $record])),
                        ])->alignment(Alignment::Center)->fullWidth()->grow(false)
                            ->visible(fn(Program $record): bool => in_array($record->status, ['open', 'draft'])),
                        Infolists\Components\Actions::make([
                            Infolists\Components\Actions\Action::make('share')
                                ->label('Share')
                                ->size('sm')
                                ->icon('heroicon-s-share')
                                ->link()
                                ->extraAttributes([
                                    'class' => 'mt-4',
                                ])
                                ->url(fn(Program $record
                                ): string => "mailto:?to=&subject=Invitation%20to%20Apply%20for%20".$record->name."%20at%20Flow%20Accelerator&body=Dear%20,%20I%20would%20like%20to%20invite%20you%20to%20apply%20for%20the%20".$record->name."%20program%20at%20Flow%20Accelerator.%20The%20program%20is%20designed%20to%20help%20you%20achieve%20your%20goals%20and%20make%20a%20positive%20impact%20in%20the%20world.%20You%20can%20learn%20more%20about%20the%20program%20and%20apply%20by%20visiting%20the%20following%20link:%20"
                                             .ProgramResource::getUrl('view', [$record])),
                        ])->alignment(Alignment::Center)->fullWidth()->grow(false)
                            ->visible(fn(Program $record): bool => in_array($record->status, ['open', 'draft'])),
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
        return auth()->id() <= 6;
    }

    public static function canEdit(Model $record): bool
    {
        return auth()->id() <= 6;
    }

    public static function canDelete(Model $record): bool
    {
        return auth()->id() == 1;
    }

    public static function getEloquentQuery(): Builder
    {
        return auth()->id() <= 6 ? parent::getEloquentQuery()->whereIn('status', ['open', 'draft', 'in review'])
            : parent::getEloquentQuery()->where('status', 'open')->whereDate('open_date', '<=', now());
    }
}

<?php

namespace App\Filament\Resources;

use App\Filament\Resources\JobResource\Pages;
use App\Filament\Resources\JobResource\RelationManagers;
use App\Models\Job;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\Split;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;
use Filament\Infolists;
use Filament\Resources\Resource;
use Filament\Support\Enums\Alignment;
use Filament\Support\Enums\FontWeight;
use Filament\Tables;
use Filament\Tables\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\HtmlString;

class JobResource extends Resource
{
    protected static ?string $model = Job::class;

    protected static ?string $navigationIcon = 'heroicon-o-briefcase';

    protected static ?int $navigationSort = 4;


    public static function getNavigationGroup(): ?string
    {
        return __('Jobs');
    }

    public static function getNavigationLabel(): string
    {
        return __('Jobs');
    }

    public static function getPluralModelLabel(): string
    {
        return __('Jobs');
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('title')->label('Title')->required()->columnSpan(2),
                DatePicker::make('open_date')->native(false)->label('Open Date')->required(),
                DatePicker::make('close_date')->native(false)->label('Close Date')->required(),
                Select::make('status')->label('Status')->options([
                    'draft'         => 'Draft',
                    'open'          => 'Open',
                    'in review'     => 'In Review',
                    'decision made' => 'Decision Made'
                ])->default('open')->required()->columnSpanFull(),
                RichEditor::make('description')->label('Description')->required()->columnSpan(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('title')->translateLabel(),
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
            ])
            ->actions([
                Action::make('apply')->label('Apply')->button()->icon('heroicon-s-plus')
                    ->url(fn(Job $record): string => JobApplicationResource::getUrl('create', ['job' => $record]))
                    ->visible(fn(Job $record): bool => $record->open_date->isPast() && $record->close_date->isFuture()),
                Tables\Actions\ViewAction::make()->button(),
                Tables\Actions\EditAction::make()->button()->color('gray'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\ForceDeleteBulkAction::make(),
                    Tables\Actions\RestoreBulkAction::make(),
                ]),
            ])
            ->emptyStateHeading(__('There are no open jobs at the moment, please check back later.'));
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
                                ->url(fn(Job $record): string => JobApplicationResource::getUrl('create',
                                    ['job' => $record]))
                                ->visible(fn(Job $record
                                ): bool => $record->open_date->isPast() && $record->close_date->isFuture()),
                        ])->fullWidth(),
                    ]),
                    Section::make([
                        TextEntry::make('title')->label('Job Title')
                            ->weight(FontWeight::Bold),
                        TextEntry::make('open_date')->date('l, M j, Y'),
                        TextEntry::make('close_date')->date('l, M j, Y'),
                        TextEntry::make('status')->formatStateUsing(fn(string $state): string => ucwords($state, '- ')),
                        Infolists\Components\Actions::make([
                            Infolists\Components\Actions\Action::make('apply')
                                ->label('Apply')
                                ->size('xl')
                                ->icon('heroicon-s-plus')
                                ->url(fn(Job $record): string => JobApplicationResource::getUrl('create',
                                    ['job' => $record]))
                                ->visible(fn(Job $record
                                ): bool => $record->open_date->isPast() && $record->close_date->isFuture()),
                        ])->alignment(Alignment::Center)->fullWidth()->grow(false),
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
            'index'  => Pages\ListJobs::route('/'),
            'create' => Pages\CreateJob::route('/create'),
            'view'   => Pages\ViewJob::route('/{record}'),
            'edit'   => Pages\EditJob::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return auth()->id() <= 6 ? parent::getEloquentQuery()->whereIn('status', ['open', 'draft'])
            : parent::getEloquentQuery()->where('status', 'open')->whereDate('open_date', '<=', now());
    }

    public static function canCreate(): bool
    {
        return auth()->id() == 1;
    }

    public static function canEdit(Model $record): bool
    {
        return auth()->id() == 1;
    }

    public static function canDelete(Model $record): bool
    {
        return auth()->id() == 1;
    }

}

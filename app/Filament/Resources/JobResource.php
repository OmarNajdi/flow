<?php

namespace App\Filament\Resources;

use App\Filament\Resources\JobResource\Pages;
use App\Filament\Resources\JobResource\RelationManagers;
use App\Models\Job;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\RichEditor;
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
            ])
            ->filters([
            ])
            ->actions([
                Action::make('apply')->label('Apply')->button()->icon('heroicon-s-plus')
                    ->url(fn(Job $record): string => JobApplicationResource::getUrl('create', ['job' => $record])),
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
                                ->url(fn(Job $record): string => ApplicationResource::getUrl('create',
                                    ['job' => $record])),
                        ])->fullWidth(),
                    ]),
                    Section::make([
                        TextEntry::make('title')->label('Job Title')
                            ->weight(FontWeight::Bold),
                        TextEntry::make('open_date')->date('l, M j, Y'),
                        TextEntry::make('close_date')->date('l, M j, Y'),
                        Infolists\Components\Actions::make([
                            Infolists\Components\Actions\Action::make('apply')
                                ->label('Apply')
                                ->size('xl')
                                ->icon('heroicon-s-plus')
                                ->url(fn(Job $record): string => JobApplicationResource::getUrl('create',
                                    ['job' => $record])),
                        ])->alignment(Alignment::Center)->fullWidth()->grow(false),
                        Infolists\Components\Actions::make([
                            Infolists\Components\Actions\Action::make('share')
                                ->label('Share')
                                ->size('sm')
                                ->icon('heroicon-s-share')
                                ->link()
                                ->extraAttributes([
                                    'class' => 'mt-4',
                                ])
                                ->url(fn(Job $record
                                ): string => "mailto:?to=&subject=Invitation%20to%20Apply%20for%20".$record->name."%20at%20Flow%20Accelerator&body=Dear%20,%20I%20would%20like%20to%20invite%20you%20to%20apply%20for%20the%20".$record->name."%20program%20at%20Flow%20Accelerator.%20The%20program%20is%20designed%20to%20help%20you%20achieve%20your%20goals%20and%20make%20a%20positive%20impact%20in%20the%20world.%20You%20can%20learn%20more%20about%20the%20program%20and%20apply%20by%20visiting%20the%20following%20link:%20"
                                             .JobResource::getUrl('view', [$record])),
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
        return parent::getEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
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

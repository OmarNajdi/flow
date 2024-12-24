<?php

namespace App\Filament\Widgets;

use Filament\Widgets\Widget;

class ApplyWidget extends Widget
{
    protected static ?int $sort = 3;

    protected static bool $isLazy = false;

    protected int|string|array $columnSpan = 'full';

    protected static string $view = 'filament.widgets.apply_widget';

//    public static function canView(): bool
//    {
//        return false;
//    }

}

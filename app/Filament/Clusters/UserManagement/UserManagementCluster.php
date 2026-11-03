<?php

namespace App\Filament\Clusters\UserManagement;

use BackedEnum;
use Filament\Clusters\Cluster;
use Filament\Pages\Enums\SubNavigationPosition;
use Filament\Support\Icons\Heroicon;
use UnitEnum;

class UserManagementCluster extends Cluster
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedIdentification;

    protected static ?SubNavigationPosition $subNavigationPosition = SubNavigationPosition::Top;

}

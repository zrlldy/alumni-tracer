<?php

namespace App\Models;

use App\Filament\Pages\GetStarted;
use Filament\Clusters\Cluster;
use Filament\Facades\Filament;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Role extends Model
{
    use SoftDeletes;

    protected $guarded = [];

    protected $casts = [
        'action' => 'array',
        'access_page' => 'array',
        'access_subpage' => 'array',
        'access_widget' => 'array',
    ];

    public static function actionOptions(): array
    {
        return [
            'import' => 'Import',
            'export' => 'Export',
            'download' => 'Download',
            'view' => 'View',
            'create' => 'Create',
            'edit' => 'Edit',
            'attach' => 'Attach',
            'detach' => 'Detach',
            'associate' => 'Associate',
            'dissociate' => 'Dissociate',
            'delete' => 'Delete',
            'force_delete' => 'Force Delete',
            'restore' => 'Restore',
        ];
    }

    public static function accessPageOptions(): array
    {
        $pages = self::pageOptions();

        // $pages['chat'] ??= 'Chat';
        // $pages['inbox'] ??= 'Inbox';
        // $pages['file-manager'] ??= 'File Manager';
        $pages['widget-grid'] ??= 'Customize Dashboard';

        return [
            'super_admin' => 'Super Admin',
        ] + $pages;
    }

    public static function pageOptions(): array
    {
        $pages = [];

        $panel = Filament::getCurrentPanel();

        if (! $panel) {
            return $pages;
        }

        /*
        * Profile
        */
        // if ($panel->hasProfile()) {
        //     $pages['settings'] = 'Settings';
        // }

        /*
        * Pages
        */
        foreach ($panel->getPages() as $page) {

            if (is_subclass_of($page, Cluster::class)) {
                continue;
            }

            if ($page === GetStarted::class) {
                continue;
            }

            $pages[$page::getSlug()] = $page::getNavigationLabel();
        }

        /*
        * Resources
        */
        foreach ($panel->getResources() as $resource) {
            $pages[$resource::getSlug()] = $resource::getNavigationLabel();
        }

        return $pages;
    }

    public static function accessSubPageOptions(): array
    {
        $subpages = [];

        $panel = Filament::getCurrentPanel();

        if (!$panel) {
            return $subpages;
        }

        foreach ($panel->getResources() as $resource) {

            if (!method_exists($resource, 'getRelations')) {
                continue;
            }

            foreach ($resource::getRelations() as $relationManager) {
                $subpages[$relationManager] = static::classLabel($relationManager, 'RelationManager');
            }
        }

        return $subpages;
    }

        public static function accessWidgetOptions(): array
    {
        $widgets = [];

        $panel = Filament::getCurrentPanel();

        if (!$panel) {
            return $widgets;
        }

        foreach ($panel->getWidgets() as $widget) {
            $widgets[$widget] = static::classLabel($widget, 'Widget');
        }

        foreach ($panel->getResources() as $resource) {

            if (!method_exists($resource, 'getWidgets')) {
                continue;
            }

            foreach ($resource::getWidgets() as $widget) {
                $widgets[$widget] = static::classLabel($widget, 'Widget');
            }
        }

        return $widgets;
    }

    protected static function classLabel(string $class, string $stripSuffix): string
    {
        $name = class_basename($class);

        if (Str::endsWith($name, $stripSuffix)) {
            $name = Str::beforeLast($name, $stripSuffix);
        }

        return Str::headline($name);
    }


}

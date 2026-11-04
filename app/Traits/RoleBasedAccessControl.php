<?php

declare(strict_types=1);

namespace App\Traits;

use App\Models\Alumni;
use App\Support\SystemSecurity;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;

/**
 * Role-based page, action, widget, and record authorization.
 *
 * Bootstrap super-admin accounts are identified centrally by SystemSecurity.
 * Other users are restricted by their role and UserAllocation management scope.
 *
 * @method static string getSlug()
 */
trait RoleBasedAccessControl
{
    /**
     * Determine whether the current user can view a record.
     */
    public static function canView($record): bool
    {
        return static::hasPageAccess()
            && static::hasPermission('view')
            && static::canAccessRecord($record);
    }

    /**
     * Determine whether the current user can create records.
     *
     * Validate the submitted record's program / alumni assignment separately,
     * because no record exists yet at this point.
     */
    public static function canCreate(): bool
    {
        return static::hasPageAccess()
            && static::hasPermission('create');
    }

    /**
     * Determine whether the current user can edit a record.
     */
    public static function canEdit($record): bool
    {
        return static::hasPageAccess()
            && static::hasPermission('edit')
            && static::canAccessRecord($record);
    }

    /**
     * Determine whether the current user can attach a relationship to a record.
     */
    public static function canAttach($record): bool
    {
        return static::hasPageAccess()
            && static::hasPermission('attach')
            && static::canAccessRecord($record);
    }

    /**
     * Determine whether the current user can detach a relationship from a record.
     */
    public static function canDetach($record): bool
    {
        return static::hasPageAccess()
            && static::hasPermission('detach')
            && static::canAccessRecord($record);
    }

    /**
     * Determine whether the current user can associate a relationship with a record.
     */
    public static function canAssociate($record): bool
    {
        return static::hasPageAccess()
            && static::hasPermission('associate')
            && static::canAccessRecord($record);
    }

    /**
     * Determine whether the current user can dissociate a relationship from a record.
     */
    public static function canDissociate($record): bool
    {
        return static::hasPageAccess()
            && static::hasPermission('dissociate')
            && static::canAccessRecord($record);
    }

    /**
     * Determine whether the current user can update a record.
     */
    public static function canUpdate($record): bool
    {
        return static::hasPageAccess()
            && static::hasPermission('edit')
            && static::canAccessRecord($record);
    }

    /**
     * Determine whether the current user can delete a record.
     */
    public static function canDelete($record): bool
    {
        return static::hasPageAccess()
            && static::hasPermission('delete')
            && static::canAccessRecord($record);
    }

    /**
     * Determine whether the current user can permanently delete a record.
     */
    public static function canForceDelete($record): bool
    {
        return static::hasPageAccess()
            && static::hasPermission('force_delete')
            && static::canAccessRecord($record);
    }

    /**
     * Determine whether the current user can restore a record.
     */
    public static function canRestore($record): bool
    {
        return static::hasPageAccess()
            && static::hasPermission('restore')
            && static::canAccessRecord($record);
    }

    /**
     * Determine whether the current user can import records.
     */
    public static function canImport(): bool
    {
        return static::hasPageAccess()
            && static::hasPermission('import');
    }

    /**
     * Determine whether the current user can export records.
     */
    public static function canExport(): bool
    {
        return static::hasPageAccess()
            && static::hasPermission('export');
    }

    /**
     * Determine whether the current user can download records or files.
     */
    public static function canDownload(): bool
    {
        return static::hasPageAccess()
            && static::hasPermission('download');
    }

    /**
     * Check access to a page using its configured slug.
     */
    public static function canAccessPageSlug(string $slug): bool
    {
        $user = Auth::user();

        if (! $user) {
            return false;
        }

        if (static::isSuperAdmin($user)) {
            return true;
        }

        $role = $user->userAllocation?->role;

        if (! $role) {
            return false;
        }

        $pages = $role->access_page ?? [];

        return in_array('super_admin', $pages, true)
            || in_array($slug, $pages, true);
    }

    /**
     * Check access to a sub-page / relation manager declared on this class.
     */
    protected static function hasSubPageAccess(): bool
    {
        $user = Auth::user();

        if (! $user) {
            return false;
        }

        if (static::isSuperAdmin($user)) {
            return true;
        }

        $role = $user->userAllocation?->role;

        if (! $role) {
            return false;
        }

        if (in_array('super_admin', $role->access_page ?? [], true)) {
            return true;
        }

        return in_array(static::class, $role->access_subpage ?? [], true);
    }

    /**
     * Check access to a specific relation manager class.
     */
    protected static function hasSubPageAccessFor(string $relationManagerClass): bool
    {
        $user = Auth::user();

        if (! $user) {
            return false;
        }

        if (static::isSuperAdmin($user)) {
            return true;
        }

        $role = $user->userAllocation?->role;

        if (! $role) {
            return false;
        }

        if (in_array('super_admin', $role->access_page ?? [], true)) {
            return true;
        }

        return in_array($relationManagerClass, $role->access_subpage ?? [], true);
    }

    /**
     * Check access to a relation manager for a particular owner record.
     */
    public static function canViewForRecord($ownerRecord, string $pageClass): bool
    {
        return static::hasSubPageAccess()
            && static::canAccessRecord($ownerRecord);
    }

    /**
     * Check whether the current user can access a widget declared on this class.
     */
    protected static function hasWidgetAccess(): bool
    {
        $user = Auth::user();

        if (! $user) {
            return false;
        }

        if (static::isSuperAdmin($user)) {
            return true;
        }

        $role = $user->userAllocation?->role;

        if (! $role) {
            return false;
        }

        if (in_array('super_admin', $role->access_page ?? [], true)) {
            return true;
        }

        return in_array(static::class, $role->access_widget ?? [], true);
    }

    /**
     * Public Filament-compatible widget authorization method.
     */
    public static function canViewWidget(): bool
    {
        return static::hasWidgetAccess();
    }

    /**
     * Check whether the current user has a specific action permission.
     */
    protected static function hasPermission(string $action): bool
    {
        $user = Auth::user();

        if (! $user) {
            return false;
        }

        if (static::isSuperAdmin($user)) {
            return true;
        }

        $role = $user->userAllocation?->role;

        if (! $role) {
            return false;
        }

        if (in_array('super_admin', $role->access_page ?? [], true)) {
            return true;
        }

        return in_array($action, $role->action ?? [], true);
    }

    /**
     * Get the permission slug for the resource / page using this trait.
     */
    protected static function getPermissionSlug(): string
    {
        if (method_exists(static::class, 'getSlug')) {
            return static::getSlug();
        }

        return class_basename(static::class);
    }

    /**
     * Check whether the current user can access this page / resource.
     */
    protected static function hasPageAccess(): bool
    {
        $user = Auth::user();

        if (! $user) {
            return false;
        }

        if (static::isSuperAdmin($user)) {
            return true;
        }

        $role = $user->userAllocation?->role;

        if (! $role) {
            return false;
        }

        $pages = $role->access_page ?? [];

        if (in_array('super_admin', $pages, true)) {
            return true;
        }

        return in_array(static::getPermissionSlug(), $pages, true);
    }

    /**
     * Find the Alumni record associated with the current user.
     */
    protected static function getAlumni(?object $user = null): ?Alumni
    {
        $user ??= Auth::user();

        if (! $user || blank($user->student_id ?? null)) {
            return null;
        }

        return Alumni::query()
            ->where('student_number', $user->student_id)
            ->first();
    }

    /**
     * Check whether the current user can access a specific record according
     * to their management scope.
     */
    protected static function canAccessRecord($record): bool
    {
        $user = Auth::user();

        if (! $user || ! is_object($record)) {
            return false;
        }

        if (static::isSuperAdmin($user)) {
            return true;
        }

        $allocation = $user->userAllocation;

        if (! $allocation) {
            return false;
        }

        switch ($allocation->management_scope) {
            case 'no_access':
                return false;

            case 'my_records':
                $alumni = static::getAlumni($user);

                if (! $alumni) {
                    return false;
                }

                // Records with an alumni_id belong to the referenced Alumni row.
                if (isset($record->alumni_id)) {
                    return (int) $record->alumni_id === (int) $alumni->id;
                }

                return isset($record->id)
                    && (int) $record->id === (int) $alumni->id;

            // my_department is retained as a compatibility alias because the
            // existing trait used it for the same program_id-based restriction.
            case 'my_department':
            case 'my_program':
                $alumni = static::getAlumni($user);

                if (! $alumni || $alumni->program_id === null) {
                    return false;
                }

                $recordProgramId = static::resolveProgramId($record);

                return $recordProgramId !== null
                    && $recordProgramId === (int) $alumni->program_id;

            case 'specified_program':
                $recordProgramId = static::resolveProgramId($record);

                return $recordProgramId !== null
                    && $allocation->programs()
                        ->whereKey($recordProgramId)
                        ->exists();

            case 'all_program':
                return true;

            default:
                return false;
        }
    }

    /**
     * Apply the current user's management scope to an Eloquent query.
     */
    protected static function applyManagementScope($query)
    {
        $user = Auth::user();

        if (! $user) {
            return $query->whereRaw('1 = 0');
        }

        if (static::isSuperAdmin($user)) {
            return $query;
        }

        $allocation = $user->userAllocation;

        if (! $allocation) {
            return $query->whereRaw('1 = 0');
        }

        $alumni = static::getAlumni($user);

        $model = $query->getModel();
        $table = $model->getTable();
        $columns = Schema::getColumnListing($table);

        switch ($allocation->management_scope) {
            case 'no_access':
                return $query->whereRaw('1 = 0');

            case 'my_records':
                if (! $alumni) {
                    return $query->whereRaw('1 = 0');
                }

                if (in_array('alumni_id', $columns, true)) {
                    return $query->where('alumni_id', $alumni->id);
                }

                if (in_array('id', $columns, true)) {
                    return $query->where('id', $alumni->id);
                }

                return $query->whereRaw('1 = 0');

            // my_department is retained as a compatibility alias because the
            // existing trait used it for the same program_id-based restriction.
            case 'my_department':
            case 'my_program':
                $programId = $alumni?->program_id;

                if ($programId === null) {
                    return $query->whereRaw('1 = 0');
                }

                if (in_array('program_id', $columns, true)) {
                    return $query->where('program_id', $programId);
                }

                if (in_array('alumni_id', $columns, true)) {
                    return $query->whereHas(
                        'alumni',
                        fn ($relationQuery) => $relationQuery->where('program_id', $programId),
                    );
                }

                return $query->whereRaw('1 = 0');

            case 'specified_program':
                $programIds = $allocation->programs()->pluck('program.id');

                if ($programIds->isEmpty()) {
                    return $query->whereRaw('1 = 0');
                }

                if (in_array('program_id', $columns, true)) {
                    return $query->whereIn('program_id', $programIds);
                }

                if (in_array('alumni_id', $columns, true)) {
                    return $query->whereHas(
                        'alumni',
                        fn ($relationQuery) => $relationQuery->whereIn('program_id', $programIds),
                    );
                }

                return $query->whereRaw('1 = 0');

            case 'all_program':
                return $query;

            default:
                return $query->whereRaw('1 = 0');
        }
    }

    /**
     * Resolve a record's program ID directly or through its Alumni relation.
     */
    protected static function resolveProgramId($record): ?int
    {
        if (! is_object($record)) {
            return null;
        }

        if (isset($record->program_id)) {
            return (int) $record->program_id;
        }

        if (isset($record->alumni_id) || method_exists($record, 'alumni')) {
            $programId = $record->alumni?->program_id;

            return $programId === null ? null : (int) $programId;
        }

        return null;
    }

    /**
     * Determine whether the current user has the my_records management scope.
     * Bootstrap super-admins are treated as having unrestricted scope.
     */
    protected static function isMyRecordsScope(): bool
    {
        $user = Auth::user();

        if (! $user) {
            return false;
        }

        if (static::isSuperAdmin($user)) {
            return true;
        }

        $allocation = $user->userAllocation;

        if (! $allocation) {
            return false;
        }

        return $allocation->management_scope === 'my_records';
    }

    /**
     * Use SystemSecurity as the single source of truth for bootstrap
     * super-admin identity throughout this trait.
     */
    protected static function isSuperAdmin(?object $user = null): bool
    {
        return SystemSecurity::isBootstrapSuperAdmin($user ?? Auth::user());
    }
}

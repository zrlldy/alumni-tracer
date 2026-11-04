<?php
// app/Support/RememberedAccountsCookie.php
namespace App\Support;

use App\Models\User;

class RememberedAccountsCookie
{
    public const COOKIE_NAME = 'remembered_accounts';

    public const DAYS = 90;

    public static function get(): array
    {
        $raw = request()->cookie(self::COOKIE_NAME);

        if (blank($raw)) {
            return [];
        }

        $decoded = json_decode($raw, true);

        return is_array($decoded) ? $decoded : [];
    }

    public static function remember(User $user): array
    {
        $map = self::prune(self::get());

        $map[(string) $user->id] = [
            'name' => $user->name,
            'email' => $user->email,
            'avatar' => method_exists($user, 'getFilamentAvatarUrl') ? $user->getFilamentAvatarUrl() : null,
        ];

        return $map;
    }

    public static function forget(array $map, int|string $userId): array
    {
        unset($map[(string) $userId]);

        return $map;
    }

    /**
     * Drop entries for users that no longer exist, are soft-deleted,
     * or can no longer access this panel.
     */

    public static function prune(array $map): array
    {
        if (empty($map)) {
            return $map;
        }

        $validIds = User::query()
            ->whereIn('id', array_keys($map))
            ->get()
            ->filter(fn (User $user) => ! method_exists($user, 'canAccessPanel')
                || $user->canAccessPanel(\Filament\Facades\Filament::getCurrentOrDefaultPanel()))
            ->pluck('id')
            ->map(fn ($id) => (string) $id)
            ->all();

        return array_filter(
            $map,
            fn ($userId) => in_array((string) $userId, $validIds, true), // cast key too
            ARRAY_FILTER_USE_KEY,
        );
    }

    public static function queue(array $map): void
    {
        if (empty($map)) {
            cookie()->queue(cookie()->forget(self::COOKIE_NAME));

            return;
        }

        cookie()->queue(cookie()->make(
            self::COOKIE_NAME,
            json_encode($map),
            60 * 24 * self::DAYS,
            path: '/',
            secure: true,
            httpOnly: true, // must be readable... actually keep false only if JS needs it; we don't, so:
            sameSite: 'lax',
        ));
    }
}
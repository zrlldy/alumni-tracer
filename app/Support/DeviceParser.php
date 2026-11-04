<?php

namespace App\Support;

use DeviceDetector\DeviceDetector;
use DeviceDetector\Parser\Device\AbstractDeviceParser;

class DeviceParser
{
    /**
     * Parse a user agent string into a friendly device label + metadata.
     *
     * @return array{
     *     label: string,
     *     brand: ?string,
     *     model: ?string,
     *     deviceName: ?string,
     *     os: string,
     *     osVersion: ?string,
     *     browser: string,
     *     browserVersion: ?string,
     *     isMobile: bool,
     *     isTablet: bool,
     *     isDesktop: bool,
     * }
     */
    public static function parse(?string $userAgent, array $hints = []): array
    {
        $userAgent ??= '';

        // Point the parser at Matomo's bundled regex database.
        AbstractDeviceParser::setVersionTruncation(AbstractDeviceParser::VERSION_TRUNCATION_NONE);

        $dd = new DeviceDetector($userAgent);
        $dd->parse();

        $osInfo = $dd->getOs();
        $clientInfo = $dd->getClient();

        // Client Hints (Sec-CH-UA-Platform / -Platform-Version) are more
        // accurate than the UA string when the browser sent them, since
        // Chrome no longer puts real OS/model info in the plain UA.
        $os = $hints['platform'] ?? ($osInfo['name'] ?? 'Unknown OS');
        $osVersion = $hints['platformVersion'] ?? ($osInfo['version'] ?? null);

        $browser = $clientInfo['name'] ?? 'Unknown browser';
        $browserVersion = $clientInfo['version'] ?? null;

        $brand = $dd->getBrandName() ?: null;
        $model = $dd->getModel() ?: null;

        $isMobile = $dd->isSmartphone();
        $isTablet = $dd->isTablet();
        $isDesktop = $dd->isDesktop();

        // Sec-CH-UA-Model gives the real device model directly, e.g.
        // "V2244" (a Vivo model code) or "SM-G991B" (Samsung) — this is
        // the ONLY reliable source now that Chrome hides it from the UA
        // string. Prefer it outright over whatever Matomo guessed.
        if (filled($hints['model'] ?? null)) {
            $model = $hints['model'];
            $brand ??= null; // Client Hints don't separate brand from model; leave brand as Matomo found it, if any.
        }

        // Build the best available "device name":
        // - Phone/tablet: "Apple iPhone 15 Pro", "Samsung Galaxy S23"
        // - Desktop: fall back to OS, since UA-string freezing means
        //   browsers no longer expose real hardware model info.
        $deviceName = trim(($brand ?? '') . ' ' . ($model ?? '')) ?: null;

        if (! $deviceName) {
            $deviceName = $isDesktop ? $os : ($os !== 'Unknown OS' ? $os : 'Unknown device');
        }

        $osWithVersion = $osVersion ? "{$os} {$osVersion}" : $os;

        $label = $deviceName && ($isMobile || $isTablet)
            ? "{$browser} on {$deviceName} ({$osWithVersion})"
            : "{$browser} on {$os}";

        return [
            'label' => $label,
            'brand' => $brand,
            'model' => $model,
            'deviceName' => $deviceName,
            'os' => $os,
            'osVersion' => $osVersion,
            'browser' => $browser,
            'browserVersion' => $browserVersion,
            'isMobile' => $isMobile,
            'isTablet' => $isTablet,
            'isDesktop' => $isDesktop,
        ];
    }
}
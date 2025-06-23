<?php

declare(strict_types=1);

use GuzzleHttp\Client;

class FreeDesktopScanner implements MimeTypeScannerInterface
{
    private const string URL = 'https://gitlab.freedesktop.org/xdg/shared-mime-info/-/raw/master/data/freedesktop.org.xml.in';

    public function scan(MimeTypeInfoMap $map): void
    {
        $httpClient = new Client();
        $freeDesktopXml = $httpClient->get(self::URL)->getBody()->getContents();
        foreach (new SimpleXMLElement($freeDesktopXml) as $mimeTypeXml) {
            $type = (string) $mimeTypeXml->attributes()->type;
            $map->addType($type);

            foreach ($mimeTypeXml->alias as $alias) {
                $aliasType = (string) $alias->attributes()->type;
                $map->addAliasType($aliasType, $type);
            }

            foreach ($mimeTypeXml->glob as $glob) {
                $extension = ltrim((string) $glob->attributes()->pattern, '*.');
                $map->addExtensionToType($extension, $type);
            }
        }
    }
}
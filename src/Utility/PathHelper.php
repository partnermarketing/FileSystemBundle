<?php

namespace Partnermarketing\FileSystemBundle\Utility;

class PathHelper
{
    /**
     * Return whether two paths are equal, ignore their file extension.
     *
     * @param  string $path1
     * @param  string $path2
     * @return string
     */
    public static function arePathsEqualIgnoringFileExtension($path1, $path2)
    {
        return self::removeFileExtensionAndKeepQueryString($path1)
            === self::removeFileExtensionAndKeepQueryString($path2);
    }

    /**
     * Remove the file extension from the given path. The query string, if there is one, will be
     * preserved.
     *
     * @param  string $path E.g. http://example/path/file.jpg?foo=bar
     * @return string       E.g. http://example/path/file?foo=bar
     */
    public static function removeFileExtensionAndKeepQueryString($path)
    {
        if (strpos($path, '.') === false) {
            return $path;
        }

        $pathParts = explode('.', $path);
        $extensionAndQueryString = $pathParts[count($pathParts) - 1];
        $extensionAndQueryStringParts = explode('?', $extensionAndQueryString, 2);
        unset($pathParts[count($pathParts) - 1]);

        $withoutExtensionAndWithoutQueryString = implode('.', $pathParts);

        if (count($extensionAndQueryStringParts) === 2) {
            return $withoutExtensionAndWithoutQueryString . '?' . $extensionAndQueryStringParts[1];
        } else {
            return $withoutExtensionAndWithoutQueryString;
        }
    }
}

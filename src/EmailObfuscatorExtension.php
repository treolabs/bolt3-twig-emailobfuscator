<?php

namespace Bolt\Extension\ZinitSolutionsGmbH\EmailObfuscator;

use Bolt\Asset\File\JavaScript;
use Bolt\Extension\SimpleExtension;

/**
 * EmailObfuscatorExtension extension class.
 */
class EmailObfuscatorExtension extends SimpleExtension
{
    /**
     * {@inheritdoc}
     */
    protected function registerAssets()
    {
        $asset = new JavaScript('/extensions/vendor/zinitsolutionsgmbh/bolt3-twig-emailobfuscator/EmailObfuscator.js');

        return [
            $asset,
        ];
    }

    /**
     * @return array
     */
    protected function registerTwigFilters()
    {
        return ['obfuscateEmail' => ['obfuscateEmail', ['is_safe' => ['html']]]];
    }
    
    /**
     * @param string $string
     * @return string
     */
    function obfuscateEmail($string)
    {
        // Casting $string to a string allows passing of objects implementing the __toString() magic method.
        $string = (string) $string;
        // Safeguard string.
        $safeguard = '$%$!!$%$';
        // Safeguard several stuff before parsing.
        $prevent = array(
            '/<input [^>]*@[^>]*>/is', // <input>
            '/(<textarea(?:[^>]*)>)(.*?)(<\/textarea>)/is', // <textarea>
            '/(<head(?:[^>]*)>)(.*?)(<\/head>)/is', // <head>
            '/(<script(?:[^>]*)>)(.*?)(<\/script>)/is', // <script>
        );
        foreach ($prevent as $pattern) {
            $string = preg_replace_callback($pattern, function ($matches) use ($safeguard) {
                return str_replace('@', $safeguard, $matches[0]);
            }, $string);
        }
        // Define patterns for extracting emails.
        $patterns = array(
            '/\<a[^>]+href\=\"mailto\:([^">?]+)(\?[^?">]+)?\"[^>]*\>(.*?)\<\/a\>/ism', // mailto anchors
            '/[_a-z0-9-]+(?:\.[_a-z0-9-]+)*@[a-z0-9-]+(?:\.[a-z0-9-]+)*(?:\.[a-z]{2,3})(?=[^>]*(<|$))/i', // plain emails
        );
        foreach ($patterns as $pattern) {
            $string = preg_replace_callback($pattern, function ($parts) use ($safeguard) {
                // Clean up element parts.
                $parts = array_map('trim', $parts);
                // ROT13 implementation for JS-enabled browsers
                $js = '<script>Rot13.write(' . "'" . str_rot13($parts[0]) . "'" . ');</script>';
                // Only for JS browsers
                $nojs = '<noscript><span>Please activate the JavaScript to see the e-mail!</span></noscript>';
                // Safeguard the obfuscation so it won't get picked up by the next iteration.
                return str_replace('@', $safeguard, $js . $nojs);
            }, $string);
        }
        // Revert all safeguards.
        return str_replace($safeguard, '@', $string);
    }
}

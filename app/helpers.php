<?php

function session_tz()
{
    return session()->get('TIMEZONE', 'UTC');
}

function command_exists($name)
{
    return Arr::has(\Artisan::all(), $name);
}

function get_keywords_from_string($string) {
    preg_match_all("/[a-z0-9\-]{3,}/i", $string, $outputArray);

    if (is_array($outputArray) && count($outputArray[0])) {
        return strtolower(implode(',', $outputArray[0]));
    }

    return '';
}

function get_domain($url) {
    $pieces = parse_url($url);

    $domain = isset($pieces['host']) ? $pieces['host'] : '';

    if (preg_match('/(?P<domain>[a-z0-9][a-z0-9\-]{1,63}\.[a-z\.]{2,6})$/i', $domain, $regs)) {
        return $regs['domain'];
    }

    return false;
}

function stripHttpScheme($url)
{
    return str_replace(['https://', 'http://'], '', $url);
}

function addHttpScheme($url, $scheme = 'http://')
{
    return parse_url($url, PHP_URL_SCHEME) === null ? $scheme.$url : $url;
}

function convertToHttpScheme($url, $scheme = 'http://')
{
    $url = stripHttpScheme($url);

    return addHttpScheme($url, $scheme);
}

// Returns true if file has multiple images in it
function is_gif($filename)
{
    $filecontents = file_get_contents($filename);

    $str_loc = 0;
    $count = 0;

    // There is no point in continuing after we find a 2nd frame
    while ($count < 2) {
        $where1 = strpos($filecontents, "\x00\x21\xF9\x04", $str_loc);

        if (!$where1) {
            break;
        } else {
            $str_loc = $where1 + 1;
            $where2 = strpos($filecontents, "\x00\x2C", $str_loc);
            if (!$where2) {
                break;
            } else {
                if ($where1 + 8 == $where2) {
                    ++$count;
                }
                $str_loc = $where2 + 1;
            }
        }
    }

    return $count > 1;
}

function recurse_copy($src, $dst)
{
    $dir = opendir($src);

    @mkdir($dst);

    while (false !== ( $file = readdir($dir)) ) {
        if (( $file != '.' ) && ( $file != '..' )) {
            if ( is_dir($src . '/' . $file) ) {
                recurse_copy($src . '/' . $file,$dst . '/' . $file);
            }
            else {
                copy($src . '/' . $file,$dst . '/' . $file);
            }
        }
    }

    closedir($dir);
}

?>

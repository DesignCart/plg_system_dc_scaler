<?php
    defined('_JEXEC') or die;

    use JShrink\Minifier;
    use Joomla\CMS\Uri\Uri;


    function dcScalerMinifyJs($code){
        if (trim($code) === '') {
            return $code;
        }

        require_once __DIR__ . '/../vendor/JShrink/Minifier.php';

        try {
            return Minifier::minify($code, ['flaggedComments' => false]);
        } catch (\Throwable $e) {
            return $code;
        }
    }


    function dcScalerProcessJs($body, $excluded){
        if (!preg_match_all(
            '#<script[^>]+src=["\']([^"\']+)["\'][^>]*></script>#i',
            $body,
            $matches
        )) {
            return $body;
        }

        $fullTags = $matches[0];   
        $srcList  = $matches[1];  

        if (empty($srcList)) {
            return $body;
        }

        $combined = '';

        foreach ($srcList as $url) {
            $path = dcScalerUriToPath($url);

            if (!$path || !is_file($path)) {
                continue;
            }

            $norm = dcScalerNormalizePath($url);

            if (in_array($norm, $excluded)) {
                continue;
            }

            $js = @file_get_contents($path);
            if ($js === false || $js === '') {
                continue;
            }

            $combined .= "\n" . $js . ';';
        }

        if ($combined === '') {
            return $body;
        }

        $minified = dcScalerMinifyJs($combined);

        $cacheDir = JPATH_ROOT . '/media/dc_scaler/cache/';
        dcScalerEnsureDir($cacheDir);   

        $target = $cacheDir . 'merged.min.js';
        @file_put_contents($target, $minified);

        foreach ($fullTags as $tag) {
            $body = str_replace($tag, '', $body);
        }

        $src = rtrim(Uri::root(true), '/')
            . '/media/dc_scaler/cache/merged.min.js?v=' . time();

        $inject = '<script src="' . $src . '"></script>';

        if (stripos($body, '</body>') !== false) {
            $body = preg_replace('~</body>~i', $inject . "\n</body>", $body, 1);
        } else {
            $body .= "\n" . $inject;
        }

        return $body;
    }

    function dcScalerJsIsExcluded($url){
        foreach (PlgSystemDc_scaler::$excludeJs as $rule) {
            if ($rule !== '' && strpos($url, $rule) !== false) {
                return true;
            }
        }
        return false;
    }

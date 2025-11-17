<?php
    defined('_JEXEC') or die;

    use Joomla\CMS\Uri\Uri;

    function dcScalerMinifyCssString(string $css): string{

        $css = preg_replace('!/\*.*?\*/!s', '', $css);
        $css = preg_replace('/\n\s*\n/', "\n", $css);
        $css = str_replace(["\r", "\n", "\t"], '', $css);
        $css = preg_replace('/\s+/', ' ', $css);
        $css = preg_replace('/\s*([{};:,])\s*/', '$1', $css);
        $css = preg_replace('/;}/', '}', $css);

        return trim($css);
    }


    function dcScalerProcessCss($styles, $excluded): void{
        $cssFiles  = [];
        $inlineCss = '';

        foreach ($styles as $name => $asset) {

            $uri = $asset->getUri() ?? '';

            $norm = dcScalerNormalizePath($uri);

            if (in_array($norm, $excluded)) {
                continue; 
            }

            if ($asset->getOption('inline')) {
                $inlineCss .= $asset->getOption('content') . "\n";
                continue;
            }

            if ($uri === '') {
                continue;
            }

            $path = dcScalerUriToPath($uri);
            if ($path && is_file($path)) {
                $cssFiles[] = $path;
            }
        }

        if (empty($cssFiles) && trim($inlineCss) === '') {
            return;
        }

        $content = '';

        foreach ($cssFiles as $path) {
            $css = @file_get_contents($path);
            if ($css !== false) {
                $content .= "\n" . $css;
            }
        }

        if ($inlineCss !== '') {
            $content .= "\n/* INLINE */\n" . $inlineCss;
        }

        $minified = dcScalerMinifyCssString($content);

        $cacheDir = JPATH_ROOT . '/media/dc_scaler/cache/';
        dcScalerEnsureDir($cacheDir);

        $file = $cacheDir . 'merged.min.css';
        @file_put_contents($file, $minified);

    }


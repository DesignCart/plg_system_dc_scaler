<?php
    defined('_JEXEC') or die;

    use Joomla\CMS\Uri\Uri;


    function dcScalerUriToPath(string $uri): ?string{
        if ($uri === '') {
            return null;
        }

        $clean = explode('?', $uri, 2)[0];

        $rootFull = rtrim(Uri::root(), '/');    
        $rootPath = rtrim(Uri::root(true), '/'); 

        if (preg_match('#^https?://#i', $clean)) {

            if (stripos($clean, $rootFull) === 0) {
                $clean = substr($clean, strlen($rootFull));
            } else {
                return null;
            }
        }

        if ($rootPath !== '' && strpos($clean, $rootPath . '/') === 0) {
            $clean = substr($clean, strlen($rootPath)); 
        }

        if ($clean === '' || $clean[0] !== '/') {
            $clean = '/' . $clean;
        }

        $path = JPATH_ROOT . $clean;

        return is_file($path) ? $path : null;
    }

    function dcScalerEnsureDir(string $dir): void{
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
    }

    function dcScalerNormalizePath($path) {
        $path = str_replace('\\', '/', $path);
        $path = preg_replace('#/+#', '/', $path);

        $root = str_replace('\\', '/', JPATH_ROOT);
        $path = str_replace($root, '', $path);

        return ltrim($path, '/');
    }







<?php


    use Joomla\CMS\Plugin\CMSPlugin;
    use Joomla\CMS\Factory;
    use Joomla\CMS\Uri\Uri;
    use Joomla\CMS\Response\JsonResponse;

    class PlgSystemDc_scaler extends CMSPlugin{
        protected $app;
        protected $excludeCss = array();
        protected $excludeJs = array();

        public function __construct(&$subject, $config){
            parent::__construct($subject, $config);

            $this->excludeCss = $this->prepareExcludeList($this->params->get('exclude_css', ''));
            $this->excludeJs  = $this->prepareExcludeList($this->params->get('exclude_js', ''));
        }

        protected function prepareExcludeList($text){
            if (!trim($text)) return [];
            $lines = preg_split('/\r\n|\r|\n/', trim($text));
            return array_filter(array_map('trim', $lines));
        }

        protected function normalizePath($url){
            if (!$url) return '';

            $url = preg_replace('#\?.*$#', '', $url);
            $url = preg_replace('#^https?://[^/]+#i', '', $url);
            $root = rtrim(\Joomla\CMS\Uri\Uri::root(true), '/');
            if ($root && str_starts_with($url, $root)) {
                $url = substr($url, strlen($root));
            }

            if (!str_starts_with($url, '/')) {
                $url = '/' . $url;
            }

            return $url;
        }

        public function onBeforeCompileHead(){
            $app = \Joomla\CMS\Factory::getApplication();

            $enable_css = $this->params->get('enable_css');

            if ($app->isClient('site')) {

                $this->autoClean();
                $this->autoRefresh();

                if($enable_css){
                    $doc    = $app->getDocument();
                    $wam    = $doc->getWebAssetManager();
                    $styles = $wam->getAssets('style');

                    $cssFile = JPATH_ROOT . '/media/dc_scaler/cache/merged.min.css';

                    if (!is_file($cssFile)) {
                        require_once __DIR__ . '/helper/helpers.php';
                        require_once __DIR__ . '/helper/css.php';
                        dcScalerProcessCss($styles, $this->excludeCss);
                    }

                    $app->getDocument()->addStyleSheet(
                        rtrim(\Joomla\CMS\Uri\Uri::root(true), '/')
                        . '/media/dc_scaler/cache/merged.min.css'
                    );

                    foreach ($styles as $name => $asset) {
                        $url = $asset->getUri();

                        if (in_array($this->normalizePath($url), $this->excludeCss)) continue;

                        $wam->disableAsset('style', $name);
                    }
                }
            }
        }  
        
        public function onAfterRender(){
            $app = \Joomla\CMS\Factory::getApplication();

            $enable_js = $this->params->get('enable_js');

            if($app->isClient('site') AND $enable_js){
                $body = $app->getBody();
                
                require_once __DIR__ . '/helper/helpers.php';
                require_once __DIR__ . '/helper/js.php';

                $body = dcScalerProcessJs($body, $this->excludeJs);

                $app->setBody($body);
            }
        }
        
        private function autoClean(){
            $lifetime = (int) $this->params->get('lifetime', 60); 
            $fileCss = JPATH_ROOT . '/media/dc_scaler/cache/merged.min.css';
            $fileJs = JPATH_ROOT . '/media/dc_scaler/cache/merged.min.js';

            if (is_file($fileCss)) {
                $modified = filemtime($fileCss);
                $ageMinutes = (time() - $modified) / 60;

                if ($ageMinutes >= $lifetime) {
                    @unlink($fileCss);
                }
            }

            if (is_file($fileJs)) {
                $modified = filemtime($fileJs);
                $ageMinutes = (time() - $modified) / 60;

                if ($ageMinutes >= $lifetime) {
                    @unlink($fileJs);
                }
            }

            return;
        }


        private function autoRefresh(){
            $purge = (int) $this->params->get('purge_on_next_request', 0);

            if ($purge !== 1) {
                return;
            }

            $fileCss = JPATH_ROOT . '/media/dc_scaler/cache/merged.min.css';
            $fileJs  = JPATH_ROOT . '/media/dc_scaler/cache/merged.min.js';

            if (is_file($fileCss)) {
                @unlink($fileCss);
            }

            if (is_file($fileJs)) {
                @unlink($fileJs);
            }

            $this->params->set('purge_on_next_request', 0);

            $db = \Joomla\CMS\Factory::getDbo();
            $query = $db->getQuery(true)
                ->update('#__extensions')
                ->set('params = ' . $db->quote((string) json_encode($this->params)))
                ->where('element = ' . $db->quote('dc_scaler'))
                ->where('folder = ' . $db->quote('system'));

            $db->setQuery($query);
            $db->execute();
        }

    }

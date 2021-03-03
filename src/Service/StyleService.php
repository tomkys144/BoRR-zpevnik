<?php


namespace App\Service;


use DateTime;
use ScssPhp\ScssPhp\Compiler;
use ScssPhp\ScssPhp\Exception\CompilerException;
use Symfony\Component\Yaml\Yaml;

class StyleService
{
    /**
     * @return string
     */
    public function generateCSS(): string
    {
        $meta = Yaml::parseFile(dirname(__DIR__) . '/../public/style/meta.yml');

        if ((!isset($meta['timestamp'])) || ($meta['timestamp'] == null)) {
            $meta['timestamp'] = 0;
        }

        $datetime = new DateTime();

        $diff = abs($datetime->getTimestamp() - $meta['timestamp']) / 60;
        if ($diff > 15 || $_SERVER['HTTP_HOST'] == 'localhost:8080') {
            $compiler = new Compiler();
            $compiler->addImportPath(function($path) {
                if (!file_exists(dirname(__DIR__) . '/../public/style/scss/'.$path)) return null;
                return dirname(__DIR__) . '/../public/style/scss/'.$path;
            });

            $main = $meta['main'];
            try {
                file_put_contents(dirname(__DIR__) . '/../public/style/css.css', $compiler->compile('@import "' . $main . '";'));
                $meta['timestamp'] = $datetime->getTimestamp();
                file_put_contents(dirname(__DIR__) . '/../public/style/meta.yml', Yaml::dump($meta));
                return '<!-- Generated CSS from SCSS -->';
            } catch (CompilerException $e) {
                return $e->getMessage();
            }
        }
        return '<!-- CSS already generated -->';
    }
}
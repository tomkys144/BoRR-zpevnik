<?php


namespace App\Service;


use DateTime;
use ScssPhp\ScssPhp\Compiler;
use ScssPhp\ScssPhp\Exception\CompilerException;
use Symfony\Component\Yaml\Yaml;

class StyleService
{
    public function generateCSS()
    {
        $meta = Yaml::parseFile(dirname(__DIR__) . '/../public/style/meta.yml');

        if ((!isset($meta['timestamp'])) || ($meta['timestamp'] == null)) {
            $meta['timestamp'] = 0;
        }

        $datetime = new DateTime();

        $diff = abs($datetime->getTimestamp() - $meta['timestamp']) / 60;
        if ($diff > 15) {
            $compiler = new Compiler();
            $compiler->addImportPath(function($path) {
                if (!file_exists(dirname(__DIR__) . '/../public/style/scss/'.$path)) return null;
                return dirname(__DIR__) . '/../public/style/scss/'.$path;
            });

            $main = $meta['main'];
            try {
                file_put_contents(dirname(__DIR__) . '/../public/style/css.css', $compiler->compile('@import "' . $main . '";'));
            } catch (CompilerException $e) {
                echo ($e->getMessage());
            }
        }
    }
}
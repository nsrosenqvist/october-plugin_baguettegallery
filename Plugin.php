<?php namespace NSRosenqvist\BaguetteGallery;

use Event;
use File as Filesystem;
use Config;
use Cms\Classes\Theme;
use NSRosenqvist\BaguetteGallery\Classes\Hooks;

class Plugin extends \System\Classes\PluginBase
{
    public $require = ['OFFLINE.ResponsiveImages'];

    public function pluginDetails()
    {
        return [
            'name' => 'Baguette Gallery',
            'description' => 'A fully responsive, mobile-friendly and elegant image gallery.',
            'author' => 'Niklas Rosenqvist',
            'icon' => 'icon-leaf',
            'homepage' => 'https://www.nsrosenqvist.com/'
        ];
    }

    public function registerComponents()
    {
        return [
            'NSRosenqvist\BaguetteGallery\Components\BaguetteGallery' => 'baguetteGallery'
        ];
    }

    public function registerMarkupTags()
    {
        return [
            'functions' => [
                'baguette_responsive_attributes' => ['\NSRosenqvist\BaguetteGallery\Classes\Baguette', 'getResponsiveAttributes'],
                'baguette' => ['\NSRosenqvist\BaguetteGallery\Classes\Baguette', 'makeBaguette'],
                'baguette_thumb' => ['\NSRosenqvist\BaguetteGallery\Classes\Baguette', 'makeBaguetteThumb'],
                'baguette_gallery_thumb' => ['\NSRosenqvist\BaguetteGallery\Classes\Baguette', 'makeBaguetteGalleryThumb'],
            ]
        ];
    }

    public function register()
    {
        // Add gallery css to backend forms
        Event::listen('backend.form.extendFields', function($widget)
        {
            $pages = [
                // 'RainLab\Pages\Classes\Page',
                'RainLab\Blog\Models\Post'
            ];

            if (in_array(get_class($widget->model), $pages))
            {
                // Add our gallery styles
                $widget->getController()->addCss('/plugins/nsrosenqvist/baguettegallery/assets/css/gallery_layouts.css');
                $theme = Theme::getActiveTheme();

                // Check if the theme has a css file that needs to be included too
                if (Filesystem::isFile($theme->getPath().'/partials/baguetteGallery/galleries.css'))
                {
                    $themeDir = '/'.ltrim(Config::get('cms.themesPath'),'/').'/'.$theme->getDirName();
                    $widget->getController()->addCss($themeDir.'/partials/baguetteGallery/galleries.css');
                }
            }
        });
    }

    public function boot()
    {
        // Hook into the markdown interpreter to parse galleries
        Event::listen('markdown.beforeParse', function($data) {
            Hooks::preMarkdownHook($data);
        });
        Event::listen('markdown.parse', function($original, $data) {
            Hooks::postMarkdownHook($original, $data);
        });
    }
}

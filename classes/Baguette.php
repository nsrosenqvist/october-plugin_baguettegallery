<?php namespace NSRosenqvist\BaguetteGallery\Classes;

use System\Models\File;
use File as Filesystem;
use Cms\Classes\ComponentManager;
use Cms\Classes\ComponentPartial;
use Cms\Classes\Partial;
use Cms\Classes\Theme;
use Config;
use App;
use NSRosenqvist\BaguetteGallery\Classes\BaguetteImage;

class Baguette {

    use \System\Traits\ViewMaker;

    public static $defaultClass = "baguette-gallery";
    public static $defaultLayout = "standard";
    public static $componentName = "baguetteGallery";

    public static function getResponsiveAttributes($image)
    {
        if ( ! is_a($image, 'System\Models\File'))
            return "";

        $gallerySrcset = "";

        foreach (explode(', ', $image->getResponsiveSrcset()) as $src)
        {
            $srcsetParts = explode(' ', $src);
            $gallerySrcset .= ' data-at-'.rtrim($srcsetParts[1], 'w').'="'.$srcsetParts[0].'"';
        }

        return ltrim($gallerySrcset, ' ');
    }

    public static function makeBaguette($image, $caption = "", $class = "")
    {
        if (empty($class))
            $class = self::$defaultClass;

        $image = new BaguetteImage($image, false, $caption);

        $html = '<div class="'.$class.'">';
        $html .= $image->getImageMarkup();
        $html .= '</div>';
        return $html;
    }

    public static function makeBaguetteThumb($image, $thumb, $caption = "", $class = "")
    {
        if (empty($class))
            $class = self::$defaultClass;

        $image = new BaguetteImage($image, $thumb, $caption);

        $html = '<div class="'.$class.'">';
        $html .= $image->getImageMarkup();
        $html .= '</div>';
        return $html;
    }

    public static function makeBaguetteGalleryThumb($image, $thumb, $caption = "")
    {
        $image = new BaguetteImage($image, $thumb, $caption);
        return $image->getImageMarkup($image, $caption, $thumb);
    }

    // Only used by the markdown extension, otherwise goes through component
    public static function makeBaguetteGallery($images, $layout = "", $class = "")
    {
        // Set component properties
        if (empty($class))
            $class = self::$defaultClass;
        if (empty($layout))
            $layout = self::$defaultLayout;

        $parameters = [
            'layout' => $layout,
            'class' => $class,
            'images' => []
        ];

        foreach ($images as $key => $val)
        {
            $parameters['images'][$key] = new BaguetteImage($val, true);
        }

        // Get twig runtime
        $twig = App::make('twig');
        $partial = null;
        $result = "";

        // Create an instance of the component
        $manager = ComponentManager::instance();
        $component = $manager->makeComponent(self::$componentName, null, $parameters);
        $component->init();
        $component->alias = self::$componentName;

        // Try first to find a partial from the theme
        $overrideName = $component->alias.'/'.$parameters['layout'];

        if (Filesystem::isFile(Theme::getActiveTheme()->getPath().'/partials/'.$overrideName.'.htm'))
        {
            $partial = Partial::loadCached(Theme::getActiveTheme(), $overrideName);
        }

        // If not found we use one from the plugin
        if (is_null($partial))
        {
            if (Filesystem::isFile(plugins_path().'/nsrosenqvist/baguettegallery/components/baguettegallery/'.$parameters['layout'].'.htm'))
            {
                $partial = ComponentPartial::loadCached($component, $parameters['layout']);
            }
            else
            {
                $partial = ComponentPartial::loadCached($component, self::$defaultLayout);
            }
        }

        // Render the component
        if ( ! is_null($partial))
        {
            $template = $twig->loadTemplate($partial->getFullPath());
            $result = $template->render($parameters);
        }

        return $result;
    }

    public static function getImageMarkup($image, $caption = "", $thumb = "")
    {
        $image = self::getFile($image); // Either string or System\Models\File
        $html = "";

        // Check that we have something to work with
        if (is_null($image))
        {
            return null;
        }

        // Create anchor that opens baguette
        if (is_a($image, 'System\Models\File'))
        {
            if (empty($caption) && $caption !== false)
            {
                $caption = $image->title;
            }

            // Create an anchor with caption and baguette's responsive attributes
            $html .= '<a href="'.$image->path.'" '.self::getResponsiveAttributes($image).' data-caption="'.$caption.'" target="_blank">';
        }
        else
        {
            $html .= '<a href="'.$image.'" data-caption="'.$caption.'" target="_blank">';
        }

        // Set a thumb if it hasn't been specified (String)
        if (! empty($thumb))
        {
            $image = $thumb;
        }

        // Make a responsive element if we have a System\Models\File
        $html .= (is_a($image, 'System\Models\File')) ? self::getResponsiveMarkup($image) : '<img src="'.$image.'" alt="'.$caption.'">';

        // Return the html
        $html .= '</a>';
        return $html;
    }

    public static function getResponsiveMarkup($image)
    {
        $html = '<picture>';
        $resImages = $image->getResponsiveImages();

        foreach ($resImages as $key => $resimg)
        {
            $html .= '<source srcset="'.$resimg['path'].'" media="(max-width: '.$resimg['width'].'px)">';
        }

        $html .= '<img src="'.$image->path.'" alt="'.$caption.'">';
        $html .= '</picture>';

        return $html;
    }

    public static function canBeResponsive($path)
    {
        // If it's a File we say yes
        if (is_a($path, 'System\Models\File'))
            return true;

        // If it's an array we check if it has a path key
        if (is_array($path))
        {
            if ( ! isset($path['path']))
                return false;

            $path = $path['path'];
        }

        // We only support strings from here on
        if (is_string($path))
        {
            $urlInfo = parse_url($path);

            if (isset($urlInfo['host']) && $urlInfo['host'] == Request::root())
            {
                if (strpos($urlInfo['path'], '/uploads/'))
                {
                    return true;
                }
            }
        }

        return false;
    }

    public static function getFile($file)
    {
        // It's already a File
        if (is_a($file, 'System\Models\File'))
        {
            return $file;
        }

        // See if we can work with the array
        if (is_array($file))
        {
            if ( ! isset($file['path']))
            {
                return null;
            }

            $file = $file['path'];
        }

        // "Original" string
        $original = $file;

        // See if we can fetch a File model
        if (self::canBeResponsive($file))
        {
            $basename = basename($file);
            $file = File::where("file_name", "=", $file)->orWhere("disk_name", "=", $file)->first();

            return (is_null($file)) ? $original : $file;
        }

        return $original;
    }

    public static function getLayouts()
    {
        $layouts = [];

        foreach (Filesystem::files(Theme::getActiveTheme()->getPath().'/partials/'.self::$componentName) as $layout)
        {
            if (basename($layout) == 'galleries.css')
                continue;
            if (basename($layout) == 'default.htm')
                continue;

            $name = basename(substr($layout, 0, strrpos($layout, '.')));

            if ( ! isset($layouts[$name]))
                $layouts[$name] = $name;
        }

        foreach (Filesystem::files(plugins_path().'/nsrosenqvist/baguettegallery/components/baguettegallery/') as $layout)
        {
            if (basename($layout) == 'default.htm')
                continue;

            $name = basename(substr($layout, 0, strrpos($layout, '.')));

            if ( ! isset($layouts[$name]))
                $layouts[$name] = $name;
        }

        return $layouts;
    }
}

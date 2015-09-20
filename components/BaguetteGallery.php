<?php namespace NSRosenqvist\BaguetteGallery\Components;

use NSRosenqvist\BaguetteGallery\Classes\Baguette;
use Block;

class BaguetteGallery extends \Cms\Classes\ComponentBase
{
    protected static $includedAssets = false;
    protected static $initialized = [];

    public function componentDetails()
    {
        return [
            'name' => 'Baguette Gallery',
            'description' => 'A fully responsive, mobile-friendly and elegant image gallery.'
        ];
    }

    public function defineProperties()
    {
        return [
            'captions' => [
                 'title'             => 'Captions',
                 'description'       => 'Display image captions',
                 'default'           => true,
                 'type'              => 'checkbox'
            ],
            'buttons' => [
                 'title'             => 'Buttons',
                 'description'       => 'Display buttons',
                 'default'           => true,
                 'type'              => 'checkbox'
            ],
            'async' => [
                 'title'             => 'Async',
                 'description'       => 'Load files asynchronously',
                 'default'           => true,
                 'type'              => 'checkbox'
            ],
            'preload' => [
                 'title'             => 'Preload',
                 'description'       => 'How many files should be preloaded from current image',
                 'default'           => 2,
                 'type'              => 'string',
                 'placeholder'       => '2',
                 'validationPattern' => '^[0-9]+$',
                 'validationMessage' => 'The preload property can contain only numeric symbols'
            ],
            'animation' => [
                 'title'             => 'Animation',
                 'description'       => 'Animation type',
                 'default'           => 'slideIn',
                 'type'              => 'dropdown',
                 'options'           => [
                    false => 'none',
                    'slideIn' => 'Slide in',
                    'fadeIn' => 'Fade in'
                ]
            ],
            'initialize' => [
                 'title'             => 'Initialize',
                 'description'       => 'Can be set to false to prevent the gallery from being initialized automatically',
                 'default'           => true,
                 'type'              => 'checkbox'
            ],
            'includeAssets' => [
                 'title'             => 'Include Assets',
                 'description'       => 'Can be set to false to prevent the gallery from including it\'s shipped version of baguetteBox',
                 'default'           => true,
                 'type'              => 'checkbox'
            ],
            'class' => [
                 'title'             => 'Gallery Class',
                 'description'       => 'To use separate configuration of multiple gallery instances, they must each have their own class',
                 'default'           => Baguette::$defaultClass,
                 'type'              => 'string',
                 'placeholder'       => Baguette::$defaultClass
            ],
            'layout' => [
                 'title'             => 'Layout Template',
                 'description'       => 'The name of the gallery layout to use',
                 'default'           => Baguette::$defaultLayout,
                 'type'              => 'dropdown',
                 'options'           => Baguette::getLayouts()
            ]
        ];
    }

    public function onRun()
    {
        // Add Baguettebox assets
        if ((bool) $this->property('includeAssets') && ! self::$includedAssets)
        {
            self::$includedAssets = true;
            $this->addCss('/plugins/nsrosenqvist/baguettegallery/assets/css/baguetteBox.min.css');
            $this->addJs('/plugins/nsrosenqvist/baguettegallery/assets/js/baguetteBox.min.js');
        }

        // Add css for default gallery layouts
        $this->addCss('/plugins/nsrosenqvist/baguettegallery/assets/css/gallery_layouts.css');

        // Only initialize if we're supposed to
        if (self::isInitialized($this->property('class')) == false && (bool) $this->property('initialize'))
        {
            self::initialize($this->property('class'));
            Block::append('scripts', '<script type="text/javascript">'.$this->getInitializeScript().'</script>');
        }

    }

    public function onRender()
    {
        $this->page['class'] = $this->property('class');
        $this->page['layout'] = $this->property('layout');
        $this->page['images'] = $this->property('images');
    }

    public static function initialize($class)
    {
        self::$initialized[$class] = true;
    }

    public static function isInitialized($class)
    {
        return isset(self::$initialized[$class]);
    }

    public function getInitializeScript()
    {
        $properties = $this->getProperties();

        $properties['captions'] = (bool) $properties['captions'];
        $properties['buttons'] = (bool) $properties['buttons'];
        $properties['async'] = (bool) $properties['async'];
        $properties['preload'] = (int) $properties['preload'];
        unset($properties['class']);
        unset($properties['includeAssets']);
        unset($properties['initialize']);
        unset($properties['layout']);
        unset($properties['images']);

        return 'baguetteBox.run(".'.$this->property('class').'", '.json_encode((object) $properties).');';
    }
}

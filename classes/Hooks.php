<?php namespace Nsrosenqvist\BaguetteGallery\Classes;

use Nsrosenqvist\BaguetteGallery\Classes\Baguette;

class Hooks {

    protected static $baguettes = [];

    public function __construct()
    {
        self::$baguettes['galleries'] = [];
        self::$baguettes['singles'] = [];
    }

    protected static function resetBaguettes()
    {
        self::$baguettes['galleries'] = [];
        self::$baguettes['singles'] = [];
    }

    public static function preMarkdownHook($data) {
        self::resetBaguettes();
        $text = trim($data->text);

        // Find galleries
        $search = '@\[baguette\-gallery(.*?)\](.*?)\[\/baguette\-gallery\]@s';

        $text = preg_replace_callback(
            $search,
            function($matches) {
                $key = uniqid('baguette-gallery');

                self::$baguettes['galleries'][$key] = array(
                    'parameters' => $matches[1],
                    'content' => $matches[2],
                    'original' => '[baguette-gallery'.$matches[1].']'.$matches[2].'[/baguette-gallery]'
                );
                return $key;
            },
            $text);

        // Find singles
        $search = '@\[baguette(.*?)\](.*?)\[\/baguette\]@s';

        $text = preg_replace_callback(
            $search,
            function($matches) {
                $key = uniqid('baguette-gallery');

                self::$baguettes['singles'][$key] = array(
                    'parameters' => $matches[1],
                    'content' => $matches[2],
                    'original' => '[baguette'.$matches[1].']'.$matches[2].'[/baguette]'
                );
                return $key;
            },
            $text);

        $data->text = $text;
    }

    public static function postMarkdownHook($original, $data) {
        $text = trim($data->text);

        // Process galleries
        foreach(self::$baguettes['galleries'] as $key => $value)
        {
            $images = self::processTagContent($value['content']);
            $parameters = self::processParameters($value['parameters']);

            // Set parameters
            $layout = ( ! isset($parameters['layout'])) ? Baguette::$defaultLayout : $parameters['layout'];
            $class = ( ! isset($parameters['class'])) ? Baguette::$defaultClass : $parameters['class'];

            // Insert image
            if (count($images) > 0)
                $text = str_replace($key, Baguette::makeBaguetteGallery($images, $layout, $class), $text);
            else
                $text = str_replace($key, $value['original'], $text);
        }

        // Process singles
        foreach(self::$baguettes['singles'] as $key => $value)
        {
            $image = self::processTagContent($value['content']);
            $parameters = self::processParameters($value['parameters']);

            // Set parameters
            $caption = ( ! isset($parameters['caption'])) ? "" : $parameters['caption'];
            $class = ( ! isset($parameters['class'])) ? Baguette::$defaultClass : $parameters['class'];

            // Insert image
            if (count($image) > 0)
                $text = str_replace($key, Baguette::makeBaguette($image[0], $caption, $class), $text);
            else
                $text = str_replace($key, $value['original'], $text);
        }

        $data->text = $text;
    }

    protected static function processTagContent($content)
    {
        $images = [];

        // Extract image URLs
        if (preg_match_all('@\]\('.'(.*?)'.'\)@s', $content, $matches)) {
            $images = $matches[1];
        }

        return $images;
    }

    protected static function processParameters($parameters)
    {
        $params = [];
        $quoteChar = "";
        $gettingMultiple = false;
        $multiple = "";
        $multipleKey = "";

        foreach (explode(' ', trim($parameters, ' ')) as $paramSet)
        {
            // If we're in the process of getting a string
            if ($gettingMultiple)
            {
                // Should we stop?
                if (substr($paramSet, strlen($paramSet)-1, 1) == $quoteChar)
                {
                    $gettingMultiple = false;
                    $multiple .= ' '.rtrim($paramSet, $quoteChar);
                    $params[$multipleKey] = $multiple;
                }
                else
                {
                    $multiple .= ' '.$paramSet;
                }

                continue;
            }

            // Separate key from value
            $paramPair = explode('=', $paramSet);

            if (count($paramPair) > 1)
            {
                $firstChar = substr($paramPair[1], 0, 1);
                $lastChar = substr($paramPair[1], strlen($paramPair[1])-1, 1);

                // Check if we're working with a quoted string that should be kept intact
                if ($firstChar == '\'' || $firstChar == '"')
                {
                    $quoteChar = $firstChar;

                    // Check that it's not just a single word that's quoted
                    if ($lastChar != $firstChar)
                    {
                        $gettingMultiple = true;
                        $multipleKey = $paramPair[0];
                        $multiple = ltrim($paramPair[1], $quoteChar);
                    }
                    else
                    {
                        $params[$paramPair[0]] = trim($paramPair[1], $quoteChar);
                    }
                }
                else
                {
                    $params[$paramPair[0]] = $paramPair[1];
                }
            }
        }

        return $params;
    }
}

<?php namespace NSRosenqvist\BaguetteGallery\Classes;

use System\Models\File;
use NSRosenqvist\BaguetteGallery\Classes\Baguette;

class BaguetteImage {

    public $type;
    public $path;
    public $image;
    public $thumb;
    public $thumbType;
    public $thumbPath;
    public $caption;

    public function __construct($image, $thumb = false, $caption = true)
    {
        // These must be executed in order
        $this->type = $this->getImageType($image);
        $this->image = $this->getImage($image, $this->type);
        $this->path = $this->getPath($this->image, $this->type);
        $this->caption = $this->getCaption($caption);

        // These as well
        $thumb = (is_bool($thumb) || (is_string($thumb) && empty($thumb))) ? $this->image : $thumb;
        $this->thumbType = $this->getImageType($thumb);
        $this->thumb = $this->getImage($thumb, $this->thumbType);
        $this->thumbPath = $this->getPath($this->thumb, $this->thumbType);
    }

    public function getImageMarkup()
    {
        $html = "";

        // Create anchor that opens baguette
        if ($this->type == 'file')
            $html .= '<a href="'.$this->path.'" '.Baguette::getResponsiveAttributes($this->image).' data-caption="'.$this->caption.'" target="_blank">';
        else
            $html .= '<a href="'.$this->path.'" data-caption="'.$this->caption.'" target="_blank">';

        // Make a responsive element if we have a System\Models\File
        $html .= ($this->thumbType == 'file') ? $this->getResponsiveMarkup() : '<img src="'.$this->thumbPath.'" alt="'.$this->caption.'">';

        // Return the html
        return $html.'</a>';
    }

    protected function getPath(&$image, $type)
    {
        switch ($type)
        {
            case "file": return $image->path; break;
            case "array": return $image['path']; break;
            case "string": return $image; break;
            default: return "";
        }
    }

    protected function getResponsiveMarkup()
    {
        if ($this->thumbType != 'file')
            return "";

        $html = '<picture>';
        $resImages = $this->thumb->getResponsiveImages();

        foreach ($resImages as $key => $resimg)
        {
            $html .= '<source srcset="'.$resimg['path'].'" media="(max-width: '.$resimg['width'].'px)">';
        }

        $html .= '<img src="'.$this->thumbPath.'" alt="'.$this->caption.'">';
        $html .= '</picture>';

        return $html;
    }

    protected function getImageType($image)
    {
        // It's already a File
        if (is_a($image, 'System\Models\File'))
            return 'file';

        // See if we can work with the array
        if (is_array($image) && isset($image['path']) && isset($image['caption']))
            return 'array';

        return 'string';
    }

    protected function getCaption($caption)
    {
        if ($caption === false)
            return "";

        if (empty($caption) || $caption === true)
        {
            if ($this->type == 'file')
                return $this->image->title;
            elseif ($this->type == 'array')
                return $this->image['caption'];
        }

        return $caption;
    }

    protected function getImage($image, &$type)
    {
        if (is_a($image, 'System\Models\File'))
            return $image;

        // See if we can make a File out of an array
        if ($type == 'array')
        {
            $basename = basename($image['path']);
            $original = $image;
            $image = File::where("file_name", "=", $basename)->orWhere("disk_name", "=", $basename)->first();

            if ( ! is_null($image))
            {
                $type = 'file';
                return $image;
            }

            return $original;
        }

        return (string) $image;
    }
}

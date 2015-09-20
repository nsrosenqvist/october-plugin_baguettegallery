# Multiple instances

Baguette Gallery can be initialized manually and in several instances. If you want
to use the standard configuration for all markdown editor use, so that you don't have
to specify the class parameter, and an instance with specific settings for featured
images, you can easily create multiple instances. And also reference that instance
when creating a baguette powered image with the twig functions.

```
[baguetteGallery]

[baguetteGallery featuredGallery]
class = featured_gallery
captions = false
==
==
{% component 'featuredGallery' images=post.featured_images %}

{{ baguette(post.featured_images.first, "A single image", "featured_images") }}
```

# Component properties

See the [component file](https://github.com/nsrosenqvist/october-plugin_baguettegallery/blob/master/components/BaguetteGallery.php)
for a full overview.

# Twig

Name | Parameters | Details | Description
-----|------------|---------|------------
`baguette_responsive_attributes` | *System\Models\File* image | An instance of *System\Models\File* is needed to get the responsive image set | Returns that attributes needed to manually create responsive baguette images.
`baguette` | *mixed* image, *string* caption, *string* class | The image can be a *System\Models\File* or a string | If the image is a string and it references an uploaded file we try to create a *System\Models\File*. Otherwise we simply link the URL in the string.
`baguette_thumb` | *mixed* image, *string* thumb, *string* caption, *string* class | This function is meant to be used in combination with October's `image.thumb()` | Same as `baguette` but with the option to set a custom thumbnail.
`baguette_gallery_thumb` | *mixed* image, *string* thumb, *string* caption, *string* class |  | Same as `baguette_thumb` but is meant to be used by gallery layouts since it doesn't wrap the image in a *div* with the class.

# Custom layouts

You can easily override the default layouts by creating your own twig file in
`[theme-root]/partials/baguetteGallery` with the same name. See the ["standard"](https://github.com/nsrosenqvist/october-plugin_baguettegallery/blob/master/components/baguettegallery/standard.htm)
layout for an example on how to build one.

If you put a file called `galleries.css` in `[theme-root]/partials/baguetteGallery`
with your custom css rules then that file will be included on the backend so that
the markdown editor can properly display your gallery layout.

# Attribution

BaguetteBox is a JavaScript library created by feimosi: https://feimosi.github.io/baguetteBox.js/

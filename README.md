Baguette Gallery is a plugin that integrates the popular [BaguetteBox image gallery](https://github.com/feimosi/baguetteBox.js)
into October. It can be used in different ways but right out of the box it integrates
with October's markdown editor and makes it easy for the user to create image galleries.

# Dependencies

Baguette Gallery requires the plugin **Bedard.Resimg**.

# Usage

All you need to do is wrap the image links with `[baguette-gallery] ...images.. [/baguette-gallery]`
and it insert the images in the default gallery layout. You can also set individual
images to be opened by baguette by using the `[baguette]...[/baguette]` tags.

The attributes *"layout"*, *"caption"* and *"class"* can be set on the baguette tags.
*Layout* is for the gallery only and it sets what layout to use. This can be one of
the shipped layouts: "standard" and "square", or a theme defined layout. *Caption*
is the text you want to show with your image and this only works for single images,
not galleries. This can be a whole sentence if you wrap it with quotation marks.
*Class* is if you want a specific instance configuration to be used when running
baguette. This is for advanced users and more information is available in the
documentation file.

The component **baguetteGallery** has to be included in your layout/page somewhere
for the plugin to function.
```
[baguetteGallery]
captions = false
```

Instead of only using markdown to create galleries you can also run the component
in your theme files.

```
[baguetteGallery]
layout = square
==
==
{% component 'baguetteGallery' images=post.featured_images %}
```

For more advanced usage, see the documentation.

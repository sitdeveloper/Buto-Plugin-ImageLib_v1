# Buto-Plugin-ImageLib_v1

Bootstrap slider builder with jpg images.

Create a slider by upload images in browser. Images and text from a single folder.

Set up it as a page plugin and sign in as webmaster or webadmin. Widget examples is showing up at the backend.


## Settings

Create image folder /theme/[theme]/_my_image_folder_.

In theme settings.yml.

```
plugin_modules:  
  backend_for_image_lib_v1:  
    plugin: 'image/lib_v1'  
    settings:  
      web_dir: '/theme/[theme]/_my_image_folder_'  
```      

```
plugin:
  image:
    lib_v1:
      enabled: true
```

## Backend

Then go to /backend_for_image_lib_v1/start to upload images.

## Widgets

### Carousel

```
type: widget
data:
  plugin: image/lib_v1
  method: carousel
  data:
    web_dir: '/theme/[theme]/_my_image_folder_'
    height: 300
```
    
### List

```
type: widget
data:
  plugin: image/lib_v1
  method: list
  data:
    web_dir: '/theme/[theme]/_my_image_folder_'
```
    
    
    

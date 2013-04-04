Sitemap-bundle based on https://github.com/ouardisoft/SitemapBundle

Installation using github
=========================

in your composer.json file add this lines

```
    "repositories": [
        ...
        {
            "type": "git",
            "url":  "https://github.com/jlaso/sitemap-bundle.git"
        }
        ...
    ],
    "require": {
        ...
        "jlaso/sitemap-bundle": "1.0.*@dev",
        ...
    }
```
    

Add in your file app/AppKernel.php

```
    ...
    public function registerBundles() {
       $bundles = array(
            ...
            new Jlaso\SitemapBundle\JlasoSitemapBundle(),
            ...
    }    
```

Configuration
=============

params
-------

* **path**: this is path where you want to save sitemap file
* **entity**: Use this entity to generate my file
* **loc**: this is a sitemap tag. we can use our route to generate link.
* **lastmod**: use this param to generate lastmod tag
* **priority**: priority


example
-------

     jlaso_sitemap:
         path: "%kernel.root_dir%/../web/sitemap.xml"
         items:
            Post:
                 entity: AppCoreBundle:Post
                     loc: {route: _post, params: {post_id: id, title: slug}}
                     lastmod: updatedAt
                     priority: 0.5

     ; with language support

      jlaso_sitemap:
          path: "%kernel.root_dir%/../web/sitemap.xml"
          items:
            Post:
              entity: AppCoreBundle:Post
                  loc: {route: _post_@language, params: {post_id: id, title: slug}}
                  lastmod: updatedAt
                  priority: 0.5

     ; OR

      jlaso_sitemap:
          path: "%kernel.root_dir%/../web/sitemap.xml"
          items:
            Post:
              entity: AppCoreBundle:Post
                  loc: {route: _post, params: {post_id: id, title: slug_language, language: _language}}
                  lastmod: updatedAt
                  priority: 0.5



My route is:
_post:
  pattern: /{post_id}/{title}/

My database table
  post(id, title, slug, text, createdAt, updatedAt)

if you have not slug field and you want to generate slug from title field use this configuration

loc: {route: _post, params: {post_id: id, {field: title, class: App\CodeBundle\Inflector, method: slug}}}

In your controller
==================

     $sitemapGenerator = $this->get('sitemap.generator');
     $sitemapGenerator->generate($language);

ROUTING.YML
===========
    ; alone

    JlasoSitemapBundle:
        pattern: /sitemap.xml
        defaults: { _controller: JlasoSitemapBundle:Sitemap:generate, _method:GET }

    ; with language support

    JlasoSitemapBundle:
        pattern: /sitemap_{language}.xml
        defaults: { _controller: JlasoSitemapBundle:Sitemap:generate, _method:GET, language:'' }

    ; with entity selection for multiple sitemap generation

    JlasoSitemapBundlePost:
        pattern: /sitemap-post-_{language}.xml
        defaults: { _controller: JlasoSitemapBundle:Sitemap:generate, _method:GET, language:'en', item:'Post' }
        # the item parameter must match with one of os_sitemap.items definition in config.yml


<?php

namespace Jlaso\SitemapBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Response;

class SitemapController extends Controller
{
    public function generateAction($language='',$item='')
    {
        //$sitemapConfig = $this->container->getParameter('sitemap.generator.configs');

        $sitemapGenerator = $this->get('sitemap.generator');

        $r = $sitemapGenerator->generate($language, $item, true);

        //return new Response('<html><body><p>Created</p></body></html>');
        $response = new Response($r); //file_get_contents($sitemapConfig['path']));
        $response->headers->set('Content-Type', 'xml');
        return $response;
    }
}

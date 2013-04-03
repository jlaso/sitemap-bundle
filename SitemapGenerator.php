<?php

namespace Jlaso\SitemapBundle;

use Doctrine\ORM\EntityManager,
    DOMDocument,
    DateTime;

/**
 * @author ouardisoft
 *          Joseluis Laso <jlaso@joseluislaso.es>
 */
class SitemapGenerator
{

    /**
     *
     * @var EntityManager $em
     */
    private $em;

    /**
     * @var $router
     */
    private $router;

    /**
     *
     * @var array $configs 
     */
    private $configs;

    /**
     *
     * @param EntityManager $em
     * @param Router $router
     * @param array $configs 
     */
    function __construct(EntityManager $em, $router, $configs)
    {
        $this->em = $em;
        $this->router = $router;
        $this->configs = $configs;
    }

    public function generate($language, $item, $returnString = false)
    {
        // Create dom object
        $dom = new DOMDocument('1.0', 'UTF-8');
        $dom->formatOutput = true;
        $dom->substituteEntities = false;

        // Create <urlset> root tag
        $urlset = $dom->createElement('urlset');
        
        // Add attribute of urlset
        $xmlns = $dom->createAttribute('xmlns');
        $urlsetText = $dom->createTextNode('http://www.sitemaps.org/schemas/sitemap/0.9'); 
        $urlset->appendChild($xmlns);
        $xmlns->appendChild($urlsetText);

        $items = $this->configs['items'];

        if($item && isset($items[$item])){
            $items = array($items[$item]);
        }

        foreach($items as $item=>$itemConfig){

            $dql = sprintf('SELECT %1$s FROM %2$s %1$s', $item, $itemConfig['entity']);

            if (!empty($itemConfig['where'])) {
                $dql .= ' WHERE ' . $itemConfig['where'];
            }

            $query = $this->em->createQuery($dql);

            $entities = $query->getResult();

            /*
             *  Generate <url> tags and bind them in urlset
             *  <url>
             *     <loc>link</loc>
             *     <lastmod>date</lastmod>
             *     <priority>date</priority>
             *  </url>
             */
            $tags = array('loc', 'lastmod', 'priority');
            foreach ($entities as $entity) {
                $url = $dom->createElement('url');
                foreach ($tags as $tag) {
                    $text = $dom->createTextNode(
                        $this->getTagValue($itemConfig,$entity, $tag, $language)
                    );
                    $elem = $dom->createElement($tag);
                    $elem->appendChild($text);

                    $url->appendChild($elem);
                }

                $urlset->appendChild($url);
            }
        }
        $dom->appendChild($urlset);

        if ($returnString == false)
            return $dom->save($this->configs['path']);

        return $dom->saveXML();
    }

    /**
     * 
     * @param Entity $entity
     * @param string $tag
     * @return string 
     */
    public function getTagValue($itemConfig, $entity, $tag, $language)
    {
        if (!is_array($itemConfig[$tag])) {
            $method = 'get' . ucfirst($itemConfig[$tag]);
            if (method_exists($entity, $method)) {
                $value = $entity->$method();

                if ($value instanceof DateTime) {
                    $value = $value->format('Y-m-d');
                } else {
                    $value = substr($value, 0, 100);
                }
            } else {
                $value = $itemConfig[$tag];
            }

            return $value;
        } else {
            extract($itemConfig[$tag]);

            foreach ($params as $key => $param) {
                $param = str_replace('_language',$language,$param);
                if (is_array($param)) {
                    $fields = explode(".",$param['field']);
                    $value = $entity->{'get' . ucfirst($fields[0])}();
                    for($i=1;$i<count($fields);$i++){
                        $value = $value->{'get' . ucfirst($fields[$i])}();
                    }
                    $object       = new $param['class'];
                    $params[$key] = $object->{$param['method']}($value);
                } else {
                    $fields = explode(".",$param);
                    $value = $entity->{'get' . ucfirst($fields[0])}();
                    for($i=1;$i<count($fields);$i++){
                        $value = $value->{'get' . ucfirst($fields[$i])}();
                    }
                    $params[$key] = $value;
                }
            }
            $route = str_replace('@language',$language,$route);
            return $this->router->generate($route, $params, true);
        }
    }

}

?>

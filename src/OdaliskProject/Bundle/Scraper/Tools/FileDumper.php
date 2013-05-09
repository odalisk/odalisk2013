<?php

namespace OdaliskProject\Bundle\Scraper\Tools;

use Buzz\Message;
use OdaliskProject\Bundle\Document\DcatDataset;
use Symfony\Component\DomCrawler\Crawler;
use OdaliskProject\Bundle\Entity\DatasetCriteria;
use OdaliskProject\Bundle\Entity\Dataset;

class FileDumper
{
    /**
     * The doctrine handle
     */
    protected static $doctrine;

    /**
     * Entity manager
     */
    protected static $em;

    /**
     * MongoDb manager
     */
    protected static $mongoDb;


    /**
     * General container
     */
    protected static $container;


    protected static $count = 0;
    protected static $totalCount = 0;

    protected static $mapping = array();

    protected static $base_path;

    public static function saveToDisk(Message\Request $request, Message\Response $response)
    {
        self::$count++;

        $file = array();
        $file['meta']['code'] = $response->getStatusCode();
        $file['meta']['url'] = $request->getUrl();
        $file['meta']['hash'] = md5($file['meta']['url']);

        if (200 == $file['meta']['code']) {
            $file['content'] = $response->getContent();
        } else {
            $file['content'] = "";
        }

        $platform = self::getPlatformName($file['meta']['url']);

        file_put_contents(self::$base_path . $platform . '/' . $file['meta']['hash'], json_encode($file));

        if (0 == self::$count % 100 || self::$count == self::$totalCount) {
           error_log('[Get HTML] ' . self::$count . ' / ' . self::$totalCount . ' done');
           error_log('[Get HTML] currently using ' . memory_get_usage(true) / (1024 * 1024) . 'MB of memory');
        }
    }


    public static function saveRdfToMongo(Message\Request $request, Message\Response $response)
    {
        self::$count++;
        $content = "";
        $file = array();
        $file['meta']['code'] = $response->getStatusCode();
        $file['meta']['url'] = $request->getUrl();
        $file['meta']['hash'] = md5($file['meta']['url']);

        if (200 == $file['meta']['code']) {
            $content = $response->getContent();
        }

        //Preparation of the temporary file
        $platformName  = self::getPlatformName($file['meta']['url']);
        $filename      = self::$base_path . $platformName . '/' . $file['meta']['hash'];
        file_put_contents($filename, json_encode($content));

        //Creation of a new DcatDataset in MongoDb

        $dcatDataset = new DcatDataset();
        $dcatDataset->setPortalName($platformName);

        $dcatDataset->setFile($filename);


        //Then we want to extract some information, so we load the associated platform
        //$platform = self::$container->get($platformName);

        //Creation of future SQL row
        //$dataset = new Dataset();
        //$portal  = $platform->loadPortal();
        //$dataset->setPortal($portal);

        
        //We launch the analysis of the content
        //$platform->analyseDcatContent($content, $dataset);
        //$criteria = new DatasetCriteria($dataset); 
        //$dataset->setCriteria($criteria);
        //$dcatDataset->setName($dataset->getName());
        $dm = self::$mongoDb;
        $dm->persist($dcatDataset);
        $dm->flush();
        //$insertedDataset = $dm->getRepository('OdaliskProject\Bundle\Document\DcatDataset')->findOneBy(array('name'=>$dataset->getName(),
        //                                                                                            'portalName'=>$platformName));
        //$dataset->setIdMongo($insertedDataset->getId());
        

        //End of the task 
        //$em = self::$container->get('doctrine')->getEntityManager();
        //$em->persist($criteria);
        //$em->persist($dataset);
        //$em->flush();
        //$insertedDataset = null;
        //$dataset = null;
        //$criteria = null;
        $dcatDataset = null;
        unlink($filename);

        if (0 == self::$count % 100 || self::$count == self::$totalCount) {
           error_log('[Get Rdf] ' . self::$count . ' / ' . self::$totalCount . ' done');
           error_log('[Get Rdf] currently using ' . memory_get_usage(true) / (1024 * 1024) . 'MB of memory');
        }
    }




    public static function saveUrls($urls, $portal_name)
    {
        self::verifyPortalPath($portal_name);
        $file = self::$base_path.$portal_name.'/urls.json';
        file_put_contents($file, json_encode($urls));
    }

    public static function saveRdfUrls($urls, $portal_name)
    {
        self::verifyPortalPath($portal_name);
        $file = self::$base_path.$portal_name.'/rdf.json';
        file_put_contents($file, json_encode($urls));
    }    

    public static function getUrls($portal_name)
    {
        $file = self::$base_path.$portal_name.'/urls.json';
        $data = file_get_contents($file);

        if (false === $data) {
            error_log('[Get HTML] URL file is missing. Run ./console odalisk:geturls ' . $portal_name);

            return array();
        } else {
           return json_decode($data, true);
        }
    }

    public static function ddlRdfFiles($portal_name)
    {
        $file = self::$base_path.$portal_name.'/rdf.json';
        $data = file_get_contents($file);

        if (false === $data) {
            error_log('[Get Rdf] URL file is missing. Run ./console odalisk:dcat:geturls ' . $portal_name);

            return array();
        } else {
           return json_decode($data, true);
        }
    }

    public static function verifyPortalPath($portal_name)
    {
        $path = self::$base_path . $portal_name;
        if (! is_dir($path)) {
            mkdir($path, 0755, true);
        }
    }

    public static function setBasePath($path)
    {
        self::$base_path = $path;
    }

    public static function setTotalCount($count)
    {
        self::$totalCount = $count;
    }

    public static function getTotalCount()
    {
        return self::$totalCount;
    }

    public static function addMapping($name, $url, $portal)
    {
        self::$mapping[$name] = array('url' => $url, 'portal' => $portal);
        self::verifyPortalPath($name);
    }

    public static function getPlatformName($dataset_url)
    {
        foreach (self::$mapping as $name => $data) {
            if (0 === strpos($dataset_url, $data['url'])) {
                return $name;
            }
        }
        error_log('[FileDumper] No match found for : ' . $dataset_url);
    }

    public static function setDoctrine($doctrine)
    {
        self::$doctrine = $doctrine;
        self::$em = self::$doctrine->getEntityManager();
        self::$em->getConnection()->getConfiguration()->setSQLLogger(null);
    }

    public static function setMongoDb($mongo)
    {
        self::$mongoDb = $mongo;

    }

    public static function setContainer($container)
    {
        self::$container = $container;
    }
}

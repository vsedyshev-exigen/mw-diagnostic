<?php
namespace MediaWiki;

class API {

    protected $host = '127.0.0.1';
    protected $wikiId = 'wiki';

    /**
     * Make server API address
     *
     * @param array $queryParams
     * @return string
     */
    protected function makeServerUrl($queryParams = array()) {
        $result = 'http://' . $this->host . '/' . $this->wikiId . '/api.php';
        if (isset($queryParams) and is_array($queryParams) and ($queryParams !== array())) {
            $result = $result . '?' . http_build_query($queryParams);
        }
        return $result;
    }

    public function setHost($host) {
        $this->host = $host;
    }

    public function setWikiId($wikiId) {
        $this->wikiId = $wikiId;
    }

    protected function makeRequest($url) {
        $result = file_get_contents($url);
        return $result;
    }

    /**
     * Get extensions
     *
     * @return array
     */
    public function getExtensions() {
        $result = array();
        $queryParams = array(
            'action' => 'query',
            'meta'   => 'siteinfo',
            'siprop' => 'extensions',
            'format' => 'json',
        );
        $url = $this->makeServerUrl($queryParams);
        $content = $this->makeRequest($url);
        $data = json_decode($content, true);
        if (isset($data['query']['extensions'])) {
            $result = $data['query']['extensions'];

        }
        return $result;
    }


}

// Configure
$mediaWikiHost = 'ru.wikipedia.org';
$mediaWikiId = 'w';

foreach($argv as $arg) {

    if (preg_match('#^\-\-host\=(.*)#', $arg, $match) === 1) {
        $mediaWikiHost = $match[1];
        continue;
    }

    if (preg_match('#^\-\-wikiId\=(.*)#', $arg, $match) === 1) {
        $mediaWikiId = $match[1];
        continue;
    }

    if (preg_match('#^\-\-help#', $arg, $match) === 1) {    
        echo '  --host="ru.wikipedia.org"    Set MediaWiki server host name' . PHP_EOL;
        echo '  --wikiId="w"                 Set MediaWiki server ID name' . PHP_EOL;
        exit(1);
    }

}

// Create API object calls
$mediaWikiAPI = new \MediaWiki\API();
$mediaWikiAPI->setHost($mediaWikiHost);
$mediaWikiAPI->setWikiId($mediaWikiId);
$mediaWikiExtensions = $mediaWikiAPI->getExtensions();

// Render
$pageTitle = 'Configuration on ' . $mediaWikiHost; 
echo $pageTitle . PHP_EOL;
echo str_repeat('=', strlen($pageTitle)) . PHP_EOL;
foreach($mediaWikiExtensions as $mediaWikiExtension) {
    $extName = array_key_exists('name', $mediaWikiExtension) ? $mediaWikiExtension['name'] : '???';
    $extType = array_key_exists('type', $mediaWikiExtension) ? $mediaWikiExtension['type'] : '???';
    $extVersion = array_key_exists('version', $mediaWikiExtension) ? $mediaWikiExtension['version'] : 'dev-master';

    echo sprintf("%40s %20s %20s", $extName, $extType, $extVersion) . PHP_EOL;
}
echo str_repeat('=', strlen($pageTitle)) . PHP_EOL;

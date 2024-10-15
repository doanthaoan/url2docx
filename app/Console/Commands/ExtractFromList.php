<?php

namespace App\Console\Commands;

use Attribute;
use Illuminate\Console\Command;

class ExtractFromList extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'url:list {url} {filename}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Url of the table of content';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $url = $this->argument('url');
        $htmlContent = file_get_contents($url);

        // Filter the needed content 
        try {
            $doc = new \DOMDocument();
            // Suppress any warnings/errors related to HTML parsing
            libxml_use_internal_errors(true);
            $doc->loadHTML($htmlContent);
            libxml_clear_errors();

            $xpath = new \DOMXPath($doc);

            // Find first div with with class="bbWrapper"
            // TCCT
            // $bbWrapperElement = $xpath->query('//div[contains(concat(" ", normalize-space(@class), " "), " bbWrapper ")]')->item(4);

            // Truyenwikidich

            // Case 1: Wrapper use
            // $bbWrapperElement = $xpath->query('//ul[@style="padding: 0 1.5rem"]')->item(0);
            // if ($bbWrapperElement) {

            //     $bbWrapperHTML = $doc->saveHTML($bbWrapperElement);
            //     $tmpDom = new \DOMDocument('1.0', 'UTF-8');
            //     $contentType = '<meta http-equiv="Content-Type" content="text/html; charset=utf-8">';
            //     $tmpDom->loadHTML($contentType . $bbWrapperHTML);
            //     // $body = $tmpDom->documentElement->firstChild;

            //     // $tmpHtml = $tmpDom->createDocumentFragment();
            //     // $tmpHtml->appendXML($bbWrapperHTML);
            //     // $newNode = $tmpDom->importNode($bbWrapperElement, true);
            //     // $body->appendChild($newNode);

            //     // echo $tmpDom->saveXML();
            //     $linkList = $tmpDom->getElementsByTagName("a");
            //     $urls = array();
            //     foreach($linkList as $link ) {
            //         $href = $link->getAttribute('href');
            //         array_push($urls, $href);
            //     }
            //     // echo $bbWrapperHTML;

            //     // var_dump($urls);
            //     // $filename = $this->argument('filename');
            //     // file_put_contents($filename, $bbWrapperHTML);
            //     if (count($urls) > 0) {
            //         for($i=0; $i < count($urls);$i++) {
            //             echo $i . " - Getting content from url: " . $urls[$i];
            //             $this->call('url:docx', ['url' => $urls[$i], 'filename' => $this->argument('filename')]);
            //             sleep(5);
            //         }
            //     }

            // } else {
            //     $this->error("No div with class=\"bbWrapper\" found.");
            // }

            // Case 2: chapter class use
            $chapterClass = $xpath->query('//li[@class="chapter-name"]/a/@href');
            if ($chapterClass) {
                $urls = array();
                foreach ($chapterClass as $chapter) {
                    $href = $chapter->nodeValue;
                    array_push($urls, $href);
                }
                if (count($urls) > 0) {
                    $consumedTime = 0;
                    for ($i = 0; $i < count($urls); $i++) {
                        $delayedTime = rand(4, 7);
                        echo $i . " (Delayed: " . $delayedTime . "s)" . " - Getting content from url: " . $urls[$i];
                        $timeStart = microtime(true);
                        $this->call('url:docx', ['url' => $urls[$i], 'filename' => $this->argument('filename')]);
                        $timeEnd = microtime(true);
                        $executionTime = round(($timeEnd - $timeStart), 2);
                        $this->info("(Chapter record:" . $executionTime . "s)\r");
                        $consumedTime = $consumedTime + $delayedTime;
                        sleep($delayedTime);
                    }
                    echo "Total time: " . $consumedTime . "s";
                }
            } else {
                $this->error("No chapter link found");
            }
        } catch (\Exception $e) {
            $this->error("Error parsing HTML content: " . $e->getMessage());
        }
    }
}

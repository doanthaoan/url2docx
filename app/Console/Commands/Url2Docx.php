<?php

namespace App\Console\Commands;

use DOMDocument;
use Illuminate\Console\Command;
use PhpOffice\PhpWord\IOFactory;
use PhpOffice\PhpWord\PhpWord;

class Url2Docx extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'url:docx {url} {filename}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Get html content from url and save it into a docx file';

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
        // Fetch HTML content from URL
        $url = $this->argument('url');
        // $context = stream_context_create(array('http' => array('header'=>'Connection: close\r\n')));
        // $htmlContent = file_get_contents($url, false, $context);
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:130.0) Gecko/20100101 Firefox/130.0');
        $token = 'Authorization: Bearer 1250781|k3SRzeQYJLzDre2sEXeDPY0gbsajLB0Ro0UYNICY;';
        $header = array(
            'Content-Type: application/x-www-form-urlencoded; charset=utf-8',
            'Cookie: accessToken=1250781|k3SRzeQYJLzDre2sEXeDPY0gbsajLB0Ro0UYNICY; cf_clearance=A_CbG.iZMDT7oBDFEqmN8yJARrLq8MJZPzgiiNvLFVY-1729022263-1.2.1.1-KKjBP9WVQ3X3.r54qv2dtEDqFDGNdAczRn_ftaGu9arZw7NLOvax1JZw9FJ3lk4YYfVLCqVjE5RCC.hvtxC3.Vluqxnr_oDVMGboKFDfO7PoHVpbGeNxUBvuh7YMeckfIs9KyEyrcYj25LxrozGXY982fGexmVYmGGK6N8qZAJvaS2qg4vq0.TEXsZhUuTSZSMH8xQP31g5cc19kcnnhA20H8CrcL3VD7UpoUHzzDCwob72myAPXCD9ZwLgEc5WDxBqLiRwnAQIVZq5zPjE12LWnXQbQY5PY7hWuCaMz5zlaAG4IyCtl5LCXBIp0C0nBdu6PzJfAbnHz8e7v0AB2KOKmJrKGEzeljCuiO4bcre86L5iR99SodqBwPyWowqfs'
        );
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        $htmlContent = curl_exec($ch);

        // echo $htmlContent;
        curl_close($ch);
        // die;
        $FILEPATH = './truyen/';
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
            // $bbWrapperElement = $xpath->query('//div[contains(concat(" ", normalize-space(@class), " "), " bbWrapper ")]')->item(0);

            // Truyenwikidich
            // $bbWrapperElement = $xpath->query('//div[contains(concat(" ", normalize-space(@class), " "), " bookContent ")]')->item(0);
            // 
            $bbWrapperElement = $xpath->query('//main[contains(concat(" ", normalize-space(@class), " "), "min-h-screen space-y-6 mt-6")]')->item(0);

            if ($bbWrapperElement) {

                $bbWrapperHTML = $doc->saveHTML($bbWrapperElement);

                $tmpDom = new \DOMDocument('1.0', 'UTF-8');
                $contentType = '<meta http-equiv="Content-Type" content="text/html; charset=utf-8">';
                $tmpDom->loadHTML($contentType . $bbWrapperHTML);

                $xpathToUpdate = new \DOMXPath($tmpDom);
                $filters = [
                    '//div[contains(concat(" ", normalize-space(@id), " "), " book-title ")]',

                ];
                // TCCT
                // $removeElements = $xpathToUpdate->query('//div[@style="text-align: center"]');
                // $domElementsToRemove = '';
                // foreach($removeElements as $node) {
                //     $domElementsToRemove .= $tmpDom->saveHTML($node);
                //     $node->parentNode->removeChild($node);
                // }


                // Truyenwikidich
                // Remove Elements
                // $removeElements = [
                //     $xpathToUpdate->query('//p[@class="flex justify-center space-x-2 items-center px-2"]')->item(0),
                //     $xpathToUpdate->query('//p[@class="book-title"]')->item(2),
                //     $xpathToUpdate->query('//div[@class="center ankhinho"]')->item(0),
                //     $xpathToUpdate->query('//div[@class="ankhito center"]')->item(0),
                // ];

                // foreach ($removeElements as $node) {
                //     // var_dump($node);
                //     $node->parentNode->removeChild($node);
                // }

                // Chapter element update
                $chapterTitle = $xpathToUpdate->query('//h2[@class="text-center text-gray-600 dark:text-gray-400 text-balance"]')->item(0);
                $newTitle = $tmpDom->createElement('h1', $chapterTitle->textContent);
                // var_dump($newTitle);
                $chapterTitle->parentNode->replaceChild($newTitle, $chapterTitle);

                // Save final version
                $filteredHTML = $tmpDom->saveHTML();
                // Convert HTML content to text
                // $html2text = new \Html2Text\Html2Text($bbWrapperHTML);
                // $plainText = $html2text->getText();

                // Convert plain text to UTF-8 encoding
                // $utf8Text = mb_convert_encoding($plainText, 'UTF-8', 'auto');
                // \PhpOffice\PhpWord\Shared\Html::addHtml($section, $plainText);
                // echo $plainText;
                // $section->addHtml($bbWrapperHTML);

                // $filename = 'output.docx';

                // // Save the document
                // $objWriter = IOFactory::createWriter($phpWord, 'Word2007');
                // $objWriter->save($filename);
                $filename = $this->argument('filename') ? $this->argument('filename') . '.html' : 'newfile.html';
                $file = fopen($FILEPATH . $filename, "a") or die("Unable to open file!");
                // $fileContent = file_get_contents($filename);
                // $fileContent .= $filteredHTML;
                fwrite($file, $filteredHTML);

                $this->info("File '$file' updated successfully.");
            } else {
                $this->error("No div with class=\"bbWrapper\" found.");
            }
        } catch (\Exception $e) {
            $this->error("Error parsing HTML content: " . $e->getMessage());
        }
    }
}

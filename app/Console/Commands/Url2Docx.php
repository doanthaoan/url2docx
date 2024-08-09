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
        $htmlContent = file_get_contents($url);
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
            $bbWrapperElement = $xpath->query('//div[contains(concat(" ", normalize-space(@id), " "), " bookContent ")]')->item(0);

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
                $removeElements = [
                    $xpathToUpdate->query('//p[@class="book-title"]')->item(0),
                    $xpathToUpdate->query('//p[@class="book-title"]')->item(2),
                    $xpathToUpdate->query('//div[@class="center ankhinho"]')->item(0),
                    $xpathToUpdate->query('//div[@class="ankhito center"]')->item(0),
                ];

                foreach($removeElements as $node) {
                    // var_dump($node);
                    $node->parentNode->removeChild($node);
                }

                // Chapter element update
                $chapterTitle = $xpathToUpdate->query('//p[@class="book-title"]')->item(0);
                $newTitle = $tmpDom->createElement('h1',$chapterTitle->textContent);
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
                $filename = $this->argument('filename') ? $this->argument('filename').'.html' : 'newfile.html';
                $file = fopen($FILEPATH.$filename, "a") or die("Unable to open file!");
                // $fileContent = file_get_contents($filename);
                // $fileContent .= $filteredHTML;
                fwrite($file, $filteredHTML);

                $this->info("File '$file' updated successfully.");
            } else {
                $this->error("No div with class=\"bbWrapper\" found.");
            }
        } catch (\Exception $e) {
            $this->error("Error parsing HTML content: ". $e->getMessage());
        }
        
    }
}

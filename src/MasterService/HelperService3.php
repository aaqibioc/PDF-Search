<?php

namespace App\MasterService;

use Smalot\PdfParser\Parser;

class HelperService3
{
    private $parser;
    public function __construct()
    {
        $this->parser = new Parser();
    }

    public function searchInPdf($pdfFilePath, $searchTerm)
    {
        $pdf = $this->parser->parseFile($pdfFilePath);
        $text = $pdf->getText();

        $found = (strpos($text, $searchTerm) !== false || strpos($pdfFilePath, $searchTerm) !== false);
        return $found;
    }

    public function parseContent($pdfFilePath)
    {
        $pdf = $this->parser->parseFile($pdfFilePath);
        $text = $pdf->getText();
        return $text;
    }
}
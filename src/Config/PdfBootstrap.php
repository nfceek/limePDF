<?php
namespace LimePDF\Config;

require_once __DIR__ . '/../PDF.php';
require_once __DIR__ . '/../../vendor/autoload.php';

use LimePDF\Pdf;

class PdfBootstrap
{
    public static function create(string $outputFile = 'example.pdf', string $outputType = 'I', $outputHeader = true, $outputFooter = true, string $pdfHeader = '', string $pdfSubHeader = '', string $pdfHeaderImage = ''): Pdf
    {
        $config = new ConfigManager();
        $config->loadFromArray([]); // loads defaults from ConfigManager

        $cfgArray = $config->toArray();
        $pdfConfig = [
            'author' => $cfgArray['author'],
            'creator' => $cfgArray['creator'],
            'title' => $cfgArray['title'],
            'font' => [
                'main' => [$cfgArray['fontNameMain'], $cfgArray['fontSizeMain']],
                'data' => [$cfgArray['fontNameData'], $cfgArray['fontSizeData']],
                'mono' => $cfgArray['fontMonospaced'],
            ],
                'headerString' => $cfgArray['headerString'],
                'headerLogoWidth' => $cfgArray['headerLogoWidth'],   
            'margins' => [
                'header' => $cfgArray['marginHeader'],
                'footer' => $cfgArray['marginFooter'],
                'top'    => $cfgArray['marginTop'],
                'bottom' => $cfgArray['marginBottom'],
                'left'   => $cfgArray['marginLeft'],
                'right'  => $cfgArray['marginRight'],
            ],
            'layout' => [
                'pageFormat' => $cfgArray['pageFormat'],
                'orientation' => $cfgArray['pageOrientation'],
                'unit' => $cfgArray['unit'],
                'imageScale' => $cfgArray['imageScaleRatio'],
            ],
            'meta' => [
                'subject' => $cfgArray['subject'],
                'keywords' => $cfgArray['keywords'],
            ]
        ];

        $pdf = new Pdf(
            $pdfConfig['layout']['orientation'],
            $pdfConfig['layout']['unit'],
            $pdfConfig['layout']['pageFormat'],
            true,
            'UTF-8',
            false
        );

        $pdf->setCellHeightRatio($pdfConfig['cellHeightRatio'] ?? 1.25);
        
        $curlopts = [];
        $curlopts = array_replace(
            $curlopts,
            $pdfConfig['curlOpts'] ?? []
        );

        // Standard setup
        $pdf->setCreator($pdfConfig['creator']);
        $pdf->setAuthor($pdfConfig['author']);
        $pdf->setTitle($pdfHeader);
        $pdf->setSubject($pdfConfig['meta']['subject']);
        $pdf->setKeywords($pdfConfig['meta']['keywords']);
        $pdf->setPrintHeader($outputHeader);
        $pdf->setPrintFooter($outputFooter);
        if($outputHeader = true ) {
            $pdf->setHeaderData(
                $pdfHeaderImage,
                $pdfConfig['headerLogoWidth'],
                $pdfHeader,
                $pdfSubHeader,
                array(0,64,255),
                array(0,64,128)
            );
        }

        $pdf->setDefaultMonospacedFont($pdfConfig['font']['mono']);
        $pdf->setMargins(
            $pdfConfig['margins']['left'],
            $pdfConfig['margins']['top'],
            $pdfConfig['margins']['right']
        );
        $pdf->setHeaderMargin($pdfConfig['margins']['header']);
        $pdf->setFooterMargin($pdfConfig['margins']['footer']);
        $pdf->setAutoPageBreak(true, $pdfConfig['margins']['bottom']);
        $pdf->setImageScale($pdfConfig['layout']['imageScale']);

        // set some language-dependent strings (optional)
        if (@file_exists(dirname(__DIR__, 2) . '/examples/lang/eng.php')) {
            require_once(dirname(__DIR__, 2) . '/examples/lang/eng.php');
            $pdf->setLanguageArray($l);
        }

        return $pdf;
    }
}

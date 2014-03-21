<?php
/* ==================================================
 * Wrap interface to the excel spreasheet processing
 */
namespace Cerad\Bundle\CoreBundle\Excel;

class Excel
{
    public function __construct()
    {
        // Verify still need this for game schedule date/times
        // 
        // \PHPExcel_Cell::setValueBinder( new ExcelValueBinder() );
    }
    public function newSpreadSheet()
    {
        return new \PHPExcel();
    }
    public function newWriter($ss)
    {
        return \PHPExcel_IOFactory::createWriter($ss, 'Excel5');
    }
    public function getCoordForColRow($pColumn = 0, $pRow = 1)
    {
        return \PHPExcel_Cell::stringFromColumnIndex($pColumn) . $pRow;
    }
    public function setCellHorizontalAllignment($ws, $cell, $alignment = '(center|left|right)') 
    {
        switch(strtolower($alignment)) {
            case "center":
                $align = \PHPExcel_Style_Alignment::HORIZONTAL_CENTER;
                break;
            case "left":
                $align = \PHPExcel_Style_Alignment::HORIZONTAL_LEFT;
                break;
            case "right":
                $align = \PHPExcel_Style_Alignment::HORIZONTAL_RIGHT;
                break;
            default:
                $align = \PHPExcel_Style_Alignment::HORIZONTAL_CENTER;
                break;
        }
        $ws->getStyle($cell)->getAlignment()->setHorizontal($align);
    }
    public function setColumnHorizontalAllignment($ws, $col, $rows, $alignment = '(center|left|right)') 
    {
        switch(strtolower($alignment)) {
            case "center":
                $align = \PHPExcel_Style_Alignment::HORIZONTAL_CENTER;
                break;
            case "left":
                $align = \PHPExcel_Style_Alignment::HORIZONTAL_LEFT;
                break;
            case "right":
                $align = \PHPExcel_Style_Alignment::HORIZONTAL_RIGHT;
                break;
            default:
                $align = \PHPExcel_Style_Alignment::HORIZONTAL_CENTER;
                break;
        }
        $top = $this->getCoordForColRow($col,1);
        $bot = $this->getCoordForColRow($col,$rows);
        $range = $top . ':' . $bot;
        $ws->getStyle($range)->getAlignment()->setHorizontal($align);
    }
    protected function createReaderForFile($fileName,$readDataOnly = true)
    {
        // Most common case
        $reader5 = new \PHPExcel_Reader_Excel5();
        
        $reader5->setReadDataOnly($readDataOnly);
        
        if ($reader5->canRead($fileName)) return $reader5;
 
        // Make sure have zip archive
        if (class_exists('ZipArchive')) 
        {
            $reader7 = new \PHPExcel_Reader_Excel2007();
        
            $reader7->setReadDataOnly($readDataOnly);
        
            if ($reader7->canRead($fileName)) return $reader7;
     
        }
        
        // Note that csv does not actually check for a csv file
        $readerCSV = new \PHPExcel_Reader_CSV();
        
        if ($readerCSV->canRead($fileName)) return $readerCSV;
        
        throw new Exception("No Reader found for $fileName");

    }
    public function load($fileName, $readDataOnly = true)
    {
        $reader = $this->createReaderForFile($fileName,$readDataOnly);

        return $reader->load($fileName);
    }
    public function loadx($file)
    {
        return \PHPExcel_IOFactory::load($file);
    }
    /* ==================================================
     * 23 Sep 2013
     * Put these in here
     * Tested on: xlsx
     */
    public function processTime($time)
    {
        return \PHPExcel_Style_NumberFormat::toFormattedString($time,'hh:mm:ss');
    }
    public function processDate($date)
    {
        return \PHPExcel_Style_NumberFormat::toFormattedString($date,'yyyy-MM-dd');
    }

}
 
?>
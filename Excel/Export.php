<?php

/* ========================================================================
 * Base class for exporting excel spreadsheets
 * 
 * Assume for now that Excell2007 is being written
 * 
 * Not all of the formatting stuff works properly under Excel5 aka 2003
 */
namespace Cerad\Bundle\CoreBundle\Excel;

class Export
{
    protected function createSpreadSheet()
    {
        return new \PHPExcel();
    }
    protected function createWriter($ss)
    {
        return \PHPExcel_IOFactory::createWriter($ss, 'Excel2007');
    }
    /* ======================================================================
     * Set a cell value with an optional format
     */
    protected function setCellValueByColumnAndRow($ws,$col,$row,$value,$format = null)
    {
        $ws->setCellValueByColumnAndRow($col,$row,$value);
        if (!$format) return;

        $coord = \PHPExcel_Cell::stringFromColumnIndex($col) . $row;
        
        $ws->getStyle($coord)->getNumberFormat()->setFormatCode($format);
    }
    /* ========================================================
     * Returns the excel numeric value for a given time
     */
    protected function getNumericTime($dt)
    {
        $hours   = $dt->format('H');
        $minutes = $dt->format('i');
        
       return ($hours / 24) + ($minutes / 1440);
    }
    protected function getNumericDate($dt)
    {
        $date = $dt->format('Y-m-d');
        
        return \PHPExcel_Shared_Date::stringToExcel($date);
    }
    
    /* ===============================================================
     * Make formatting across multiple sheets a bit easier
     * Might combine this later into single array
     */
    protected $columnWidths  = array();
    protected $columnCenters = array();
    protected $columnFormats = array();  // Maybe for dates and times?
    
    protected function setHeaders($ws,$headers,$row = 1)
    {
        $col = 0;
        foreach($headers as $header)
        {
            if (isset($this->columnWidths[$header]))
            {
                $ws->getColumnDimensionByColumn($col)->setWidth($this->columnWidths[$header]);
            }
            $ws->setCellValueByColumnAndRow($col,$row,$header);

            if (in_array($header,$this->columnCenters) == true)
            {
              //$coord = \PHPExcel_Cell::stringFromColumnIndex($col) . $row;
                
              //$ws->getStyle($coord)->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
              $ws->getStyle($col)->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
            }
            $col++;
        }
        return $row;
    }
    protected function setRow($ws,$map,$item,&$row)
    {
        $row++;
        $col = 0;
        foreach($map as $propName)
        {
            $ws->setCellValueByColumnAndRow($col++,$row,$item[$propName]);
        }
        return $row;
    }
 
    /* =======================================================
     * Called by controller to get the content
     */
    protected $ss;

    public function getBuffer($ss = null)
    {
        if (!$ss) $ss = $this->ss;
        if (!$ss) return null;

        $objWriter = $this->createWriter($ss); // \PHPExcel_IOFactory::createWriter($ss, 'Excel5');

        ob_start();
        $objWriter->save('php://output'); // Instead of file name

        return ob_get_clean();
    }
}
?>

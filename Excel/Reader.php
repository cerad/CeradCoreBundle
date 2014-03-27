<?php
/* ==================================================
 * Wrap interface to the excel spreasheet processing
 * 
 * 23 Sep 2013
 * Think this is depreciated 
 * Use Excel::createReaderForFile or Excel::load
 * 
 * 27 Mar 2014
 * Moved to CoreBundle
 * Still used by Loader
 */
namespace Cerad\Bundle\CoreBundle\Excel;

class Reader
{
    protected function createReaderForFile($fileName,$readDataOnly = true)
    {
        // Most common case
        $reader1 = new \PHPExcel_Reader_Excel5();
        
        $reader1->setReadDataOnly($readDataOnly);
        
        if ($reader1->canRead($fileName)) return $reader1;
 
        // Make sure have zip archive
        if (class_exists('ZipArchive')) 
        {
            $reader2 = new \PHPExcel_Reader_Excel2007();
        
            $reader2->setReadDataOnly($readDataOnly);
        
            if ($reader2->canRead($fileName)) return $reader2;
        }
        
        // Note that csv does not actually check for a csv file
        $reader3 = new \PHPExcel_Reader_CSV();
        
        if ($reader3->canRead($fileName)) return $reader3;
        
        throw new \Exception("No Reader found for $fileName");

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
     * 15 Oct 2013
     * Put these in here as well, hack for Kicks
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
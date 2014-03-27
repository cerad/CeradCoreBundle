<?php
namespace Cerad\Component\Excel;

/* ========================================================
 * 23 Sep 2013
 * This is intened to be extended by import routines
 */
class Import
{
    protected $excel;
    protected $reader; // Use reader instead of excel
    protected $items  = array();
    protected $errors = array();
    
    protected $record = array(
      // 'region'     => array('cols' => 'Region',         'req' => true,  'default' => 0),
    );
    protected $map = array();
    
    public function __construct()
    {
        $this->reader = $this->excel = new Reader();
    }
    protected function processDataRow($row)
    {
        $item = array();
        foreach($this->record as $name => $params)
        {
            if (isset($params['default'])) $default = $params['default'];
            else                           $default = null;
            $item[$name] = $default;
        }
        foreach($row as $index => $value)
        {
            if (isset($this->map[$index]))
            {
                $name = $this->map[$index];
                $item[$name] = trim($value);
            }
        }
        return $item;
    }
    protected function processHeaderRow($row)
    {
        $found  = array();
        $record = $this->record;
        foreach($row as $index => $colName)
        {
            $colName = trim($colName);
            foreach($record as $name => $params)
            {
                if (is_array($params['cols'])) $cols = $params['cols'];
                else                           $cols = array($params['cols']);
                foreach($cols as $col)
                {
                    if ($col == $colName)
                    {
                        if (isset($params['plus'])) $plus = $params['plus'];
                        else                        $plus = 0;
                        
                        $this->map[$index + $plus] = $name;
                        $found[$name] = true;
                    }
                }
            }
        }

        // Make sure all required attributes found
        foreach($record as $name => $params)
        {
            if (isset($params['req']) && $params['req'])
            {
                if (!isset($found[$name]))
                {
                    if (is_array($params['cols'])) $cols = $params['cols'];
                    else                           $cols = array($params['cols']);
                    $cols = implode(' OR ',$cols);
                    $this->errors[] = "Missing $cols";
                }
            }
        }
    }
    /* ======================================================
     * This should probably go away and become abstract
     * Or at least process parameters
     */
    public function load($inputFileName, $worksheetName = null)
    {
        $ss = $this->reader->load($inputFileName);

        if ($worksheetName) $ws = $ss->getSheetByName($worksheetName);
        else                $ws = $ss->getSheet(0);
        
        $rows = $ws->toArray();
        
        $header = array_shift($rows);
        
        $this->processHeaderRow($header);
        
        // Insert each record
        foreach($rows as $row)
        {
            $item = $this->processDataRow($row);
            
            $this->processItem($item);
        }
        return $this->items;
    }
    /* ====================================================
     * Abstract
     */
    protected function processItem($item)
    {
        print_r($item); die("\n");
    }
    /* ====================================================
     * Still kind of messy
     * Currently handles numeric excel values
     * Might want to handle csv string values?
     */
    public function processTime($time,$format = 'hh:mm:ss')
    {
        return \PHPExcel_Style_NumberFormat::toFormattedString($time,$format);
    }
    public function processDate($date, $format = 'yyyy-MM-dd')
    {
        return \PHPExcel_Style_NumberFormat::toFormattedString($date,$format);
    }
}
?>

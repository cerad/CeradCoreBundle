<?php

namespace Cerad\Bundle\CoreBundle\Doctrine;

use Doctrine\ORM\EntityRepository as BaseRepository;

class EntityRepository extends BaseRepository
{
    // Create main entity
    public function createEntity($params = array())
    {
        $entityName = $this->getEntityName();
        return new $entityName($params);
    }
    // Allow null for id
    public function find($id)
    {
        return $id ? parent::find($id) : null;
    }
    /* ==========================================================
     * Persistence
     */
    public function persist($entity) { return $this->getEntityManager()->persist($entity); }
    public function remove($entity)  { return $this->getEntityManager()->remove($entity);  }
    public function flush()          { return $this->getEntityManager()->flush();          }
    public function clear()          { return $this->getEntityManager()->clear();          }
    
    // Don't like these anymore
    public function save($entity) { return $this->getEntityManager()->persist($entity); }
    public function commit()      { return $this->getEntityManager()->flush();          }
 
    
    /* ==========================================================
     * Maybe an abstract class would be nice after all
     */
    protected function getScalerValue($criteria,$name)
    {
        if (!isset($criteria[$name])) return null;

        return $criteria[$name];
    }
    protected function getArrayValue($criteria,$name)
    {
        // Have on at all
        if (!isset($criteria[$name])) return null;
        
        $value = $criteria[$name];
        
        if (is_array($value)) return $this->processArray($value);
        
        $valuex = trim($value);
            
        // See if comma delimited
        if (strpos($valuex,',') === false) return $valuex;
            
        // Exploding does not trim
        $parts = explode(',',$valuex);
        
        if (count($parts) == 0) return null;
        if (count($parts) == 1) return trim($parts[0]);
            
        return $this->processArray($parts);
    }    
    protected function processArray(Array $values)
    {
        if (count($values) < 1) return null;
        
        /* ===========================================
         * 30 Jan 2014
         * I forget the exact use case here
         * 0 really should be valid but cannot alreay rely on it being an integer
         * 
         * This nonsense filters out 0 or null or blank values
         */
        $valuesx = array();
        array_walk($values, function($value) use (&$valuesx) 
        { 
            $value = trim($value);
            
            if (strlen($value)) $valuesx[] = $value; 
            
          //if ($value) $valuesx[] = $value; 
        });
        if (count($valuesx) < 1) return null;
        
        return $valuesx;
    }
    /* ====================================================
     * Hashes up array values
     * Keep for now but it really should only be used by the sync routines
     */
    public function hash($value)
    {
        die('abstract hash');
        if (is_array($value))
        {
            $array = $value;
            $value = null;
            
            // Trim and cat
            array_walk($array, function($val) use (&$value) { $value .= trim($val); });
        }
        $value = strtoupper(str_replace(array(' ','~','-',"'"),'',$value));
        
        return $value;
    }
    
}
?>

<?php

namespace Cerad\Bundle\CoreBundle\FormType;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
//  Symfony\Component\OptionsResolver\OptionsResolverInterface;

class DynamicFormType extends AbstractType
{
    protected $name;  // name="form[basic][refereeLevel] (basic equals name)
    protected $items;
    
    public function getName() { return $this->name; }
    
    public function __construct($name, $items)
    {
        $this->name  = $name;
        $this->items = $items;
    }
  //public function setDefaultOptions(OptionsResolverInterface $resolver)
  //{
  //    $resolver->setDefaults(array(
  //        'data_class' => 'Cerad\TournBundle\Entity\OfficialPlans'
  //    ));
  //}
    public function buildForm(FormBuilderInterface $builder, array $options)
    {   
        foreach($this->items as $name => $item)
        {
            switch($item['type'])
            {
                case 'radio':
                    
                    $attr = array('class' => 'radio-medium');
                    $builder->add($name,'choice',array(
                        'label'       => $item['label'],
                        'required'    => false,
                        'attr'        => $attr,
                        'empty_value' => false,
                        'expanded'    => true,
                        'multiple'    => false,
                        'choices'     => $item['choices'],
                    ));
                    break;
                
                case 'select':
                    
                    $attr = array();
                    $builder->add($name,'choice',array(
                        'label'       => $item['label'],
                        'required'    => false,
                        'attr'        => $attr,
                        'empty_value' => false,
                        'expanded'    => false,
                        'multiple'    => false,
                        'choices'     => $item['choices'],
                    ));
                    break;
                
                case 'text':
                    
                    $attr = array();
                    
                    if (isset($item['size'])) $attr['size'] = $item['size'];
                    
                    $builder->add($name,'text',array(
                        'label'    => $item['label'],
                        'required' => false,
                        'attr'     => $attr,
                    ));
                    break;
                    
                case 'textarea':
                    
                    $attr = array();
                    
                    if (isset($item['rows'])) $attr['rows'] = $item['rows'];
                    if (isset($item['cols'])) $attr['cols'] = $item['cols'];
                    
                    $builder->add($name,'textarea',array(
                        'label'    => $item['label'],
                        'required' => false,
                        'attr'     => $attr,
                    ));
                    break;
                    
                case 'collection':
                    
                    $dynamicType = new DynamicFormType($name,$item['items']);
                    $builder->add($name,$dynamicType,array(
                        'label'    => false,
                        'required' => false,
                    ));
                    break;
            }
        }
        return; if ($options);
    }
}
?>

<?php
/**
 * @category   Mad
 * @package    Mad_Model
 * @copyright  (c) 2007 Maintainable Software, LLC
 * @license    http://maintainable.com/framework-license.txt
 */

/**
 * The base object from which all DataObjects are extended from
 *
 * @category   Mad
 * @package    Mad_Model
 * @copyright  (c) 2007 Maintainable Software, LLC
 * @license    http://maintainable.com/framework-license.txt
 */
class Mad_Model_Serializer_Xml extends Mad_Model_Serializer_Base
{
    protected $_builder = null;

    /**
     * To keep the code as similar to Rails as possible, we use 
     * Mad_Support_Builder as a proxy to XMLWriter 
     */
    public function getBuilder()
    {
        if (!$this->_builder) {
            if (!isset($this->_options['indent'])) { 
                $this->_options['indent'] = 2; 
            }            
            $options = array('indent' => $this->_options['indent']);

            if (!empty($this->_options['builder'])) {
                $this->_builder = $this->_options['builder'];
            } else {
                $this->_builder = new Mad_Support_Builder($options);
                $this->_options['builder'] = $this->_builder;
            }

            if (empty($this->_options['skipInstruct'])) {
                $this->_builder->instruct();
                $this->_options['skipInstruct'] = true;
            }
        }
        return $this->_builder;
    }

    /**
     * return string
     */
    public function root()
    {
        if (!empty($this->_options['root'])) {
            $root = $this->_options['root'];
        } else {
            $root = $this->_record->getXmlClassName();
        }
        return $this->dasherize($root);
    }

    /**
     * Dasherize by default or if options['dasherize'] = true 
     * @return  boolean
     */
    public function isDasherized()
    {
        return !array_key_exists('dasherize', $this->_options) || !empty($this->_options['dasherize']);
    }
    
    /**
     * proxy to support dasherize
     * @param   string  $name
     * @return  string
     */
    public function dasherize($name)
    {
        return $this->isDasherized() ? Mad_Support_Inflector::dasherize($name) : $name;
    }

    /**
     * @return  array
     */
    public function getSerializableAttributes()
    {
        $attributes = array();
        foreach ($this->getSerializableAttributeNames() as $name) {
            $attributes[] = new Mad_Model_Serializer_Attribute($name, $this->_record);
        }
        return $attributes;
    }

    /**
     * @return  array
     */
    public function getSerializableMethodAttributes()
    {
        $methods = !empty($this->_options['methods']) ? $this->_options['methods'] : array();

        $methodAttributes = array();

        foreach ((array)$methods as $name) {
            if (method_exists($this->_record, $name)) {
                $methodAttributes[] = new Mad_Model_Serializer_MethodAttribute($name, $this->_record); 
            }
        }
        return $methodAttributes;
    }

    public function addAttributes()
    {
        $attributes = array_merge($this->getSerializableAttributes(), $this->getSerializableMethodAttributes());
        foreach ($attributes as $attribute) {
            $this->addTag($attribute);
        }  
    }

    /**
     * @param   Mad_Model_Serializer_Attribute
     */
    public function addTag($attribute)
    {
        $attrName  = $this->dasherize($attribute->getName());
        $attrValue = $attribute->getValue();
        $attrDecos = $attribute->getDecorations(empty($this->_options['skipTypes']));

        $tag = $this->getBuilder()->tag($attrName, $attrValue, $attrDecos);
    }
    
    /**
     * @param   string  $association
     * @param   mixed   $records
     * @param   array   $opts
     */
    public function addAssociations($association, $records, $opts)
    {
        // association collection
        if (is_array($records)) {
            $name = $this->dasherize($association);

            if (empty($records)) {
                $this->getBuilder()->tag($name, '', array('type' => 'array'));

            } else {            
                $tag = $this->getBuilder()->startTag($name, '', array('type' => 'array'));
                    $associationName = Mad_Support_Inflector::singularize($association);
                    foreach ($records as $record) {
                        $type = get_class($record) == $associationName ? null : get_class($record);
                        $options = array_merge($opts, array('root' => $associationName, 'type' => $type));
                        $record->toXml($options);
                    }
                $tag->end();
            }

        // single association
        } else {
            $records->toXml(array_merge($opts, array('root' => $association)));
        }
    }
    
    /** 
     * Use the record to build associations
     * 
     * @param   string  $association
     * @param   mixed   $records
     * @param   array   $opts
     */
    public function yieldRecords($association, $records, $opts)
    {
        $this->addAssociations($association, $records, $opts);
    }

    /** 
     * Return the serialized XML string
     * 
     * @return  string
     */
    public function serialize()
    {
        $args = array();
        if (!empty($this->_options['namespace'])) {
            $args['xmlns'] = $this->_options['namespace'];
        }
        if (!empty($this->_options['type'])) {
            $args['type'] = $this->_options['type'];
        }

        $builder = $this->getBuilder();
        $tag = $builder->startTag($this->root(), '', $args);
            $this->addAttributes();
            $this->addIncludes();
        $tag->end();

        return (string)$builder;
    }
}

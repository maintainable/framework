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
class Mad_Model_Serializer_Base
{
    protected $_record  = null;
    protected $_options = array();

    public function __construct($record, $options = array()) 
    {
        $this->_record  = $record;
        $this->_options = $options;
    }


    public function __toString()
    {
        $this->serialize();
    }
    
    /**
     * To replicate the behavior in ActiveRecord#attributes,
     * <tt>:except</tt> takes precedence over <tt>:only</tt>.  If <tt>:only</tt> is not set
     * for a N level model but is set for the N+1 level models,
     * then because <tt>:except</tt> is set to a default value, the second
     * level model can have both <tt>:except</tt> and <tt>:only</tt> set.  So if
     * <tt>:only</tt> is set, always delete <tt>:except</tt>.
    */
    public function getSerializableAttributeNames()
    {
        $attributeNames = $this->_record->attributeNames();

        // only
        if (!empty($this->_options['only'])) {
            $this->_options['except'] = null;
            $attributeNames = array_intersect($attributeNames, (array)$this->_options['only']);

        // except
        } else {
            $this->_options['only'] = null;
            $except = isset($this->_options['except']) ? (array)$this->_options['except'] : array();
            $except = array_merge($except, (array)$this->_record->inheritanceColumn());
            $this->_options['except'] = array_unique($except);
            $attributeNames = array_diff($attributeNames, $this->_options['except']);
        }
        sort($attributeNames);
        return $attributeNames;
    }

    public function getSerializableMethodNames()
    {
        $methodAttributes = array();

        $methods = isset($this->_options['methods']) ? (array)$this->_options['methods'] : array();
        foreach ($methods as $method) {
            if (is_callable(array($this->_record, $method))) {
                $methodAttributes[] = $method; 
            }
        }
        sort($methodAttributes);
        return $methodAttributes;
    }

    public function getSerializableNames()
    {
        $names = array_merge($this->getSerializableAttributeNames(), $this->getSerializableMethodNames());
        sort($names);
        return $names;
    }

    /**
     * Add associations specified via the <tt>:includes</tt> option.
     * Expects a block that takes as arguments:
     *   +association+ - name of the association
     *   +records+     - the association record(s) to be serialized
     *   +opts+        - options for the association records
     */
    public function addIncludes($serializableRecord = array())
    {
        if (isset($this->_options['include'])) {
            $includeAssociations = (array)$this->_options['include'];
            unset($this->_options['include']);
        }
        if (empty($includeAssociations)) { return $serializableRecord; }

        $baseOnlyOrExcept = array('except' => $this->_options['except'], 
                                  'only'   => $this->_options['only']);

        // associative array includes have additional options
        $includeHasOptions = !is_int(key($includeAssociations));
        $associations = $includeHasOptions ? array_keys($includeAssociations) : $includeAssociations;

        // find records for each association
        foreach ($associations as $association) {
            $assoc = $this->_record->reflectOnAssociation($association);
            $type = $assoc->getMacro();

            $method = Mad_Support_Inflector::camelize($association, 'lower');

            if ($type == 'hasMany' || $type == 'hasAndBelongsToMany') {
                $records = $this->_record->{$method}()->getCollection();

            } elseif ($type == 'hasOne' || $type == 'belongsTo') {
                $records = $this->_record->{$method}();
            }            

            if (empty($records)) { continue; }

            // options
            if ($includeHasOptions) {
                $associationOptions = $includeAssociations[$association];
            } else {
                $associationOptions = $baseOnlyOrExcept;
            }

            // sub-records
            $opts = array_merge($this->_options, $associationOptions);
            
            // multiple record association
            if (is_array($records)) {
                $serialized = array();
                foreach ($records as $record) {
                    $serializer = new self($record, $opts);
                    $serialized[] = $serializer->getSerializableRecord();
                }
                $serializableRecord[$association] = $serialized;
                    
            // single record association
            } else {
                $serializer = new self($records, $opts);
                $serializableRecord[$association] = $serializer->getSerializableRecord();
            }
        }

        $this->_options['include'] = $includeAssociations;

        return $serializableRecord;
    }
    
    public function getSerializableRecord()
    {
        $serializableRecord = array();
        
        foreach ($this->getSerializableAttributeNames() as $name) {
            $serializableRecord[$name] = $this->_record->$name;
        }
        foreach ($this->getSerializableMethodNames() as $name) {
            $serializableRecord[$name] = $this->_record->{$name}();
        }
        $serializableRecord = $this->addIncludes($serializableRecord);

        return $serializableRecord;
    }

    public function serialize()
    {
        // overwrite to implement
    }
}

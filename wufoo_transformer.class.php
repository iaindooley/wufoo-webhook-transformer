<?php
    abstract class WufooTransformer
    {
        public static $field_aliases = array('address'        => 'street address',
                                             'street'         => 'street address',
                                             'zip'            => 'postcode',
                                             'zip code'       => 'postcode',
                                             'zipcode'        => 'postcode',
                                             );

        abstract public function authenticate($handshake_key,&$normal_post);

        public function transformPost()
        {
            $structure = json_decode($_POST['FieldStructure']);
            $normal_post = array();
            $this->authenticate($_POST['HandshakeKey'],$normal_post);
            
            foreach($structure->Fields as $field)
                $this->{self::fieldType($field)}($field,$normal_post);
            
            $_POST = $normal_post;
        }
        
        public function unsupportedFieldType($field)
        {
            ob_start();
            var_dump($field);
            throw new UnsupportedFieldTypeException('Someone haxxed WufooTransformer wufoo web hook with an unsupported field type! '.ob_get_clean());
        }

        public static function fieldType($field)
        {
            $types = array('text',
                           'number',
                           'textarea',
                           'checkbox',
                           'radio',
                           'radio',
                           'select',
                           'shortname',
                           'address',
                           'date',
                           'eurodate',
                           'email',
                           'time',
                           'europhone',
                           'phone',
                           'url',
                           'money',
                           'page',
                           'section'
                           );

            return in_array($field->Type,$types) ? $field->Type : 'unsupportedFieldType';
        }

        public static function standardisedFieldName($title)
        {
            if(isset(self::$field_aliases[strtolower($title)]))
                $title = self::$field_aliases[strtolower($title)];
            
            return str_replace(' ','_',strtolower($title));
        }
        
        public static function addValue($field,&$normal_post,$value)
        {
            if(!$title = $field->Title)
                $title = $field->ID;

            $name  = self::standardisedFieldName($title);
            $normal_post[$name] = $value;
        }

        public static function standardField($field,&$normal_post)
        {
            $value = $_POST[$field->ID];
            self::addValue($field,$normal_post,$value);
        }

        public function text($field,&$normal_post)
        {
            self::standardField($field,$normal_post);
        }

        public function number($field,&$normal_post)
        {
            self::standardField($field,$normal_post);
        }

        public function textarea($field,&$normal_post)
        {
            self::standardField($field,$normal_post);
        }

        public function checkbox($field,&$normal_post)
        {
            $vals = array();
            
            foreach($field->SubFields as $sf)
            {
                if($_POST[$sf->ID])
                    $vals[] = $_POST[$sf->ID];
            }
            
            $value = implode(';',$vals);
            self::addValue($field,$normal_post,$value);
        }

        public function radio($field,&$normal_post)
        {
            self::standardField($field,$normal_post);
        }

        public function select($field,&$normal_post)
        {
            self::standardField($field,$normal_post);
        }

        public function shortname($field,&$normal_post)
        {
            $normal_post['first_name'] = $_POST[$field->SubFields[0]->ID];
            $normal_post['last_name']  = $_POST[$field->SubFields[1]->ID];
        }

        public function address($field,&$normal_post)
        {
            $normal_post['street_address']  = $_POST[$field->SubFields[0]->ID];
            $normal_post['street_address'] .= ' '.$_POST[$field->SubFields[1]->ID];
            $normal_post['suburb']          = $_POST[$field->SubFields[2]->ID];
            $normal_post['state']           = $_POST[$field->SubFields[3]->ID];
            $normal_post['postcode']        = $_POST[$field->SubFields[4]->ID];
            //WE DON'T CURRENTLY STORE COUNTRY - TODO dools May 20 2011
            $country                        = $_POST[$field->SubFields[5]->ID];
        }

        public function date($field,&$normal_post)
        {
            $value = date('Y-m-d',strtotime($_POST[$field->ID]));
            self::addValue($field,$normal_post,$value);
        }

        public function eurodate($field,&$normal_post)
        {
            $value = date('Y-m-d',$_POST[$field->ID]);
            self::addValue($field,$normal_post,$value);
        }

        public function email($field,&$normal_post)
        {
            self::standardField($field,$normal_post);
        }

        public function time($field,&$normal_post)
        {
            self::standardField($field,$normal_post);
        }

        public function europhone($field,&$normal_post)
        {
            self::standardField($field,$normal_post);
        }

        public function phone($field,&$normal_post)
        {
            self::standardField($field,$normal_post);
        }

        public function url($field,&$normal_post)
        {
            self::standardField($field,$normal_post);
        }

        public function money($field,&$normal_post)
        {
            self::standardField($field,$normal_post);
        }

        public function page($field,&$normal_post)
        {
        }
		
	public function section($field,&$normal_post)
        {
	}
    }

    class UnsupportedFieldTypeException extends Exception{}

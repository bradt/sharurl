<?php
class PHPFormWork {
    var $fieldsets;
    var $errors;
    var $encoding;
    
    function PHPFormWork($fieldsets = array(), $encoding = '') {
        $this->encoding = $encoding;
        
        $this->fieldsets = array();
        foreach($fieldsets as $fieldset) {
            $fieldset->form =& $this;
            $this->fieldsets[$fieldset->id] = $fieldset;
        }
        
        $this->errors = array();
    }
    
    function add_fieldset($fieldset) {
        $this->fieldsets[$fieldset->id] = $fieldset;
        
    }
    
    function remove_fieldset($id) {
        unset($this->fieldset[$id]);
    }
    
    function populate_values($values) {
        foreach ($this->fieldsets as $fieldset) {
            $fieldset->populate_values($values);
        }
    }
    
    function validate($values = '') {
        if (!$values)
            $values = $_POST;
        
        $this->populate_values($values);
        
        $this->errors = array();
        
        foreach ($this->fieldsets as $fieldset) {
            $this->errors = array_merge($this->errors, $fieldset->validate());
        }
        
        return $this->errors;
    }
    
    function html($print = false) {
        $out = '';
        foreach ($this->fieldsets as $fieldset) {
            $out .= $fieldset->html($print);
        }
        return $out;
    }
    
    function display() {
        $this->html(true);
    }
    
    /* Borrowed from Wordpress */
    function override_defaults($pairs, $atts) {
        $atts = (array)$atts;
        $out = array();
        foreach($pairs as $name => $default) {
            if ( array_key_exists($name, $atts) )
                $out[$name] = $atts[$name];
            else
                $out[$name] = $default;
        }
        return $out;
    }
}

class PFW_Fieldset {
    var $id;
    var $title;
    var $css;
    var $fields;
    var $errors;
    var $form;
    
    function PFW_Fieldset($id, $fields = array(), $attr = array()) {
        $this->id = $id;
        $this->errors = array();

        $this->fields = array();
        foreach ($fields as $field) {
            $field->fieldset =& $this;
            $this->fields[$field->id] = $field;
        }
        
        $attr = PHPFormWork::override_defaults(array(
            'title' => '',
            'css' => ''
        ), $attr);
        
        foreach ($attr as $var => $val)
            $this->$var = $val;
    }
    
    function add_field($field) {
        $this->fields[$field->id] = $field;
    }
    
    function remove_field($id) {
        unset($this->fields[$id]);
    }
    
    function populate_values($values) {
        foreach ($values as $id => $value) {
            if (isset($this->fields[$id])) {
                $this->fields[$id]->value = $value;
            }
        }
    }
    
    function validate() {
        $this->errors = array();
        
        foreach ($this->fields as $field) {
            $this->errors = array_merge($this->errors, $field->validate());
        }
        
        return $this->errors;
    }
    
    function html($print = false) {
        if (!$print) ob_start();
        ?>

        <fieldset id="<?php echo $this->id ?>"<?php echo ($this->css) ? ' class="' . $this->css . '"' : ''; ?>>
            <?php if ($this->title) : ?>
            <legend><?php echo $this->title ?></legend>
            <?php endif; ?>
            
            <?php
            foreach ($this->fields as $field) {
                $field->display();
            }
            ?>
            
        </fieldset>
        
        <?php
        if (!$print) return ob_get_clean();
    }
    
    function display() {
        $this->html(true);
    }
}


class PFW_Field {
    var $id;
    var $req;
    var $validation;
    var $name;
    var $lbl;
    var $tip;
    var $errors;
    var $value;
    var $fieldset;
    
    function PFW_Field($id, $attr = array()) {
        $this->id = $id;
        
        $attr = PHPFormWork::override_defaults(array(
            'req' => false,
            'validation' => null,
            'name' => $id,
            'lbl' => '',
            'tip' => '',
            'errors' => array(),
            'value' => ''
        ), $attr);
        
        foreach ($attr as $var => $val)
            $this->$var = $val;
    }
    
    function validate() {
        $this->errors = array();
        
        if ($this->req && $this->value == '') {
            $this->errors[] = $this->req;
                
            return $this->errors;
        }
        
        if (!empty($this->validation)) {
            $keys = array_keys($this->validation);

            if (is_int($keys[0])) {
                foreach ($this->validation as $validation) {
                    $this->_run_validation($validation);
                }
            }
            else {
                $this->_run_validation($this->validation);
            }
        }
        
        return $this->errors;
    }
    
    function _run_validation($validation) {
        extract($validation);
        
        // This is a function call validation
        if (isset($func)) {
            if (!isset($args)) {
                $args = array($this->value);
            }
            else {
                array_unshift($args, $this->value);
            }

            if ( is_string( $func ) && is_callable( array( 'PFW_Validation', $func ) ) ) {
                $func = array( 'PFW_Validation', $func );
            }

            if ( !call_user_func_array($func, $args) ) {
                $this->errors[] = $msg;
            }
        }
        elseif ( isset($regex) && !preg_match($regex, $this->value) ) {
            $this->errors[] = $msg;
        }
    }
    
    function html($print = false) {
        if (!$print) ob_start();
        ?>
        
        <div class="field field-text field-<?php echo $this->id; echo (!empty($this->errors)) ? ' field-error' : '' ?>">
            <label for="<?php echo $this->id ?>"><?php echo $this->lbl; echo ($this->req) ? '<span class="req">*</span>' : '' ?></label>
            <input type="text" class="text" id="<?php echo $this->id; ?>" name="<?php echo $this->name; ?>" value="<?php echo htmlentities($this->value) ?>" />
            <?php $this->display_tip(); $this->display_errors(); ?>
        </div>
            
        <?php
        if (!$print) return ob_get_clean();
    }
    
    function display() {
        $this->html(true);
    }
    
    function display_tip() {
        if ($this->tip) {
            echo '<p class="tip">', $this->tip, '</p>';
        }
    }
    
    function display_errors() {
        if (empty($this->errors))
            return '';
        
        foreach ($this->errors as $error) {
            ?>
            <p class="error"><?php echo $error; ?></p>
            <?php
        }
    }
}

class PFW_Textarea extends PFW_Field {
    var $rows;
    var $cols;
    
    function PFW_Textarea($id, $attr = array()) {
        parent::PFW_Field($id, $attr);

        $attr = PHPFormWork::override_defaults(array(
            'rows' => 10,
            'cols' => 4
        ), $attr);
        
        foreach ($attr as $var => $val)
            $this->$var = $val;
    }
    
    function html($print = false) {
        if (!$print) ob_start();
        ?>

        <div class="field field-textarea field-<?php echo $this->id; echo (!empty($this->errors)) ? ' field-error' : '' ?>">
            <label for="<?php echo $this->id ?>"><?php echo $this->lbl; echo ($this->req) ? '<span class="req">*</span>' : '' ?></label>
            <textarea id="<?php echo $this->id ?>" rows="<?php echo $this->rows ?>" cols="<?php echo $this->cols ?>" name="<?php echo $this->name ?>"><?php echo htmlentities($this->value, null, $this->fieldset->form->encoding) ?></textarea>
            <?php $this->display_tip(); $this->display_errors(); ?>
        </div>
        
        <?php
        if (!$print) return ob_get_clean();
    }
}

class PFW_Select extends PFW_Field {
    var $options;
    
    function PFW_Select($id, $attr = array()) {
        parent::PFW_Field($id, $attr);

        $attr = PHPFormWork::override_defaults(array(
            'options' => array()
        ), $attr);
        
        foreach ($attr as $var => $val)
            $this->$var = $val;
    }
    
    function html($print = false) {
        if (!$print) ob_start();
        ?>
        
        <div class="field field-select field-<?php echo $this->id; echo (!empty($this->errors)) ? ' field-error' : '' ?>">
            <label for="<?php echo $this->id ?>"><?php echo $this->lbl; echo ($this->req) ? '<span class="req">*</span>' : '' ?></label>
            <select name="<?php echo $this->name ?>" id="<?php echo $this->id ?>">
                <?php
                foreach ($this->options as $val => $txt) {
                    if ($val == $this->value) {
                        printf('<option value="%s" selected="selected">%s</option>', $val, $txt);
                    }
                    else {
                        printf('<option value="%s">%s</option>', $val, $txt);
                    }
                }
                ?>
            </select>
            <?php $this->display_tip(); $this->display_errors(); ?>
        </div>
        
        <?php
        if (!$print) return ob_get_clean();
    }
}

class PFW_Optionlist extends PFW_Select {
   
    function PFW_Optionlist($id, $attr = array()) {
        parent::PFW_Select($id, $attr);
    }
    
    function html($print = false) {
        if (!$print) ob_start();
        ?>

        <div class="field field-optionlist field-<?php echo $this->id; echo (!empty($this->errors)) ? ' field-error' : '' ?>">
            <label><?php echo $this->lbl; echo ($this->req) ? '<span class="req">*</span>' : '' ?></label>
            <ul>
                <?php
                foreach ($this->options as $val => $txt) :
                    $html_id = htmlentities($this->id . '_' . $val);
                    ?>
                    <li class="option-<?php echo $val; ?>">
                        <input type="radio" class="radio" name="<?php echo $this->name ?>"
                            id="<?php echo $html_id ?>" value="<?php echo $val ?>"
                            <?php echo ( ( $val == $this->value ) || ( is_array($this->value) && in_array($val, $this->value) ) ) ? 'checked="checked"' : '' ?> />
                        <label for="<?php echo $html_id ?>"><?php echo $txt ?></label>
                    </li>
                    <?php
                endforeach;
                ?>
            </ul>
            <?php $this->display_tip(); $this->display_errors(); ?>
        </div>
        
        <?php
        if (!$print) return ob_get_clean();
    }
}

/* The following validation functions were borrowed from Code Igniter */
class PFW_Validation {

	/**
	 * Valid Email
	 *
	 * @access	public
	 * @param	string
	 * @return	bool
	 */	
	function valid_email($str)
	{
		return ( ! preg_match("/^([a-z0-9\+_\-]+)(\.[a-z0-9\+_\-]+)*@([a-z0-9\-]+\.)+[a-z]{2,6}$/ix", $str)) ? FALSE : TRUE;
	}

	// --------------------------------------------------------------------
	
	/**
	 * Valid Emails
	 *
	 * @access	public
	 * @param	string
	 * @return	bool
	 */	
	function valid_emails($str)
	{
		if (strpos($str, ',') === FALSE)
		{
			return $this->valid_email(trim($str));
		}
		
		foreach(explode(',', $str) as $email)
		{
			if (trim($email) != '' && $this->valid_email(trim($email)) === FALSE)
			{
				return FALSE;
			}
		}
		
		return TRUE;
	}
	
	/**
	 * Alpha
	 *
	 * @access	public
	 * @param	string
	 * @return	bool
	 */		
	function alpha($str)
	{
		return ( ! preg_match("/^([a-z])+$/i", $str)) ? FALSE : TRUE;
	}
	
	// --------------------------------------------------------------------
	
	/**
	 * Alpha-numeric
	 *
	 * @access	public
	 * @param	string
	 * @return	bool
	 */	
	function alpha_numeric($str)
	{
		return ( ! preg_match("/^([a-z0-9])+$/i", $str)) ? FALSE : TRUE;
	}
	
	// --------------------------------------------------------------------
	
	/**
	 * Alpha-numeric with underscores and dashes
	 *
	 * @access	public
	 * @param	string
	 * @return	bool
	 */	
	function alpha_dash($str)
	{
		return ( ! preg_match("/^([-a-z0-9_-])+$/i", $str)) ? FALSE : TRUE;
	}
	
	// --------------------------------------------------------------------
	
	/**
	 * Numeric
	 *
	 * @access	public
	 * @param	string
	 * @return	bool
	 */	
	function numeric($str)
	{
		return (bool)preg_match( '/^[\-+]?[0-9]*\.?[0-9]+$/', $str);

	}

	// --------------------------------------------------------------------

    /**
     * Is Numeric
     *
     * @access    public
     * @param    string
     * @return    bool
     */
    function is_numeric($str)
    {
        return ( ! is_numeric($str)) ? FALSE : TRUE;
    } 

	// --------------------------------------------------------------------
	
	/**
	 * Integer
	 *
	 * @access	public
	 * @param	string
	 * @return	bool
	 */	
	function integer($str)
	{
		return (bool)preg_match( '/^[\-+]?[0-9]+$/', $str);
	}
	
	// --------------------------------------------------------------------

    /**
     * Is a Natural number  (0,1,2,3, etc.)
     *
     * @access	public
     * @param	string
     * @return	bool
     */
    function is_natural($str)
    {   
   		return (bool)preg_match( '/^[0-9]+$/', $str);
    }

	// --------------------------------------------------------------------

    /**
     * Is a Natural number, but not a zero  (1,2,3, etc.)
     *
     * @access	public
     * @param	string
     * @return	bool
     */
	function is_natural_no_zero($str)
    {
    	if ( ! preg_match( '/^[0-9]+$/', $str))
    	{
    		return FALSE;
    	}
    	
    	if ($str == 0)
    	{
    		return FALSE;
    	}
    
   		return TRUE;
    }
	
	// --------------------------------------------------------------------
	
	/**
	 * Valid Base64
	 *
	 * Tests a string for characters outside of the Base64 alphabet
	 * as defined by RFC 2045 http://www.faqs.org/rfcs/rfc2045
	 *
	 * @access	public
	 * @param	string
	 * @return	bool
	 */
	function valid_base64($str)
	{
		return (bool) ! preg_match('/[^a-zA-Z0-9\/\+=]/', $str);
	}
    
}
?>
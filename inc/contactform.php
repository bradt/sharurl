<?php
include(ROOT . '/inc/phpformwork.php');

class ContactForm extends PHPFormWork {
    var $success;
    
    function ContactForm() {
        $this->success = false;
        
        $malicious = array(
            'func' => array($this, 'malicious'),
            'msg' => "You can not use any of the following: a linebreak, or the phrases<br />'mime-version', 'content-type', 'bcc:', 'cc:' or 'to:'"
        );
                
        $fields[] = new PFW_Field('your_name', array(
            'req' => 'Please enter your name.',
            'lbl' => 'Your Name',
            'validation' => $malicious
        ));
        
        $fields[] = new PFW_Field('your_email', array(
            'req' => 'Please enter your email address.',
            'lbl' => 'Your Email',
            'validation' => array(
                array(
                    'func' => 'valid_email',
                    'msg' => 'Please enter a valid email address.'
                ),
                $malicious
            )
        ));
        
        $fields[] = new PFW_Field('subject', array(
            'req' => 'Please enter a subject.',
            'lbl' => 'Subject',
            'validation' => $malicious
        ));
        
        $fields[] = new PFW_Textarea('message', array(
            'req' => 'Please enter a message.',
            'lbl' => 'Message'
        ));
        
        $fields[] = new PFW_Optionlist('isbot', array(
            'req' => 'Sorry, no bots.',
            'lbl' => 'Is Bot?',
            'options' => array(
                '' => 'Yes',
                'No' => 'No'
            )
        ));

        if (!in_array($_POST['what'], array('plugin', 'personal'))) {
            $fieldsets[] = new PFW_Fieldset('message-details', $fields);
        }
        
        parent::PHPFormWork($fieldsets, get_siteinfo('charset'));

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $_POST = array_map('stripslashes', $_POST);
            $this->validate();
            
            if (empty($this->errors)) {
                $this->sendmail();
                $this->success = true;
            }
        }
    }
    
    function sendmail() {
        foreach ($this->fieldsets as $fieldset) {
            foreach ($fieldset->fields as $var => $field) {
                $$var = $field->value;
            }
        }
        
        $to = get_siteinfo('admin_email');
        $subject = '[' . get_siteinfo('name') . '] ' . $subject;
        $message = wordwrap($message, 80, "\n");
        
        $headers = "MIME-Version: 1.0\n";
        $headers .= "From: $your_name <$your_email>\n";
        $headers .= "Content-Type: text/plain; charset=\"" . get_siteinfo('charset') . "\"\n";
        
        mail($to, $subject, $message, $headers);
    }

    function malicious($input) {
        $is_malicious = false;
        $bad_inputs = array( "\r", "\n", "%0a", "%0d", "Content-Type:", "bcc:","to:","cc:" );
        foreach($bad_inputs as $bad_input) {
            if(stripos(strtolower($input), strtolower($bad_input)) !== false) {
                $is_malicious = true; break;
            }
        }
        return !$is_malicious;
    }

    function validate_what($str) {
        return ( !in_array( $str, array('spam','plugin','personal') ) );
    }
}
?>
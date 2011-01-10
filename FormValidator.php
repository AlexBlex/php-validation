<?php

/**
 * Form validation library.
 *
 * @see http://brettic.us/2010/06/18/form-validation-class-using-php-5-3/
 */
class FormValidator {

    private $messages = array();
    private $errors = array();
    private $rules = array();
    private $fields = array();
    private $functions = array();
    private $arguments = array();
    private $data = null;

    /**
     * Constructor.
     * Define which values to validate.
     *
     * @param array $data
     */
    function __construct($data = null) {
        $this->data = (is_null($data)) ? $_POST : $data;
    }

    // ----------------- ADD NEW RULE FUNCTIONS BELOW THIS LINE ----------------

    /**
     * Field has to be valid email address.
     *
     * @param string $message
     * @return FormValidator
     */
    public function email($message = null) {
        $this->set_rule(__FUNCTION__, function($email) {
                    return (strlen(trim($email)) === 0 || filter_var($email, FILTER_VALIDATE_EMAIL) === TRUE) ? TRUE : FALSE;
                }, $message);
        return $this;
    }

    /**
     * Field must be filled in.
     *
     * @param string $message
     * @return FormValidator
     */
    public function required($message = null) {
        $this->set_rule(__FUNCTION__, function($string) {
                    return (strlen(trim($string)) === 0) ? FALSE : TRUE;
                }, $message);
        return $this;
    }

    /**
     * Field must contain valid float value.
     *
     * @param string $message
     * @return FormValidator
     */
    public function float($message = null) {
        $this->set_rule(__FUNCTION__, function($string) {
                    return (filter_var($string, FILTER_VALIDATE_FLOAT) === FALSE) ? FALSE : TRUE;
                }, $message);
        return $this;
    }

    /**
     * Field must contain valid integer value.
     *
     * @param string $message
     * @return FormValidator
     */
    public function integer($message = null) {
        $this->set_rule(__FUNCTION__, function($string) {
                    return (filter_var($string, FILTER_VALIDATE_INT) === FALSE) ? FALSE : TRUE;
                }, $message);
        return $this;
    }

    /**
     * Every character is a digit.
     * This is just like integer(), except there is no upper limit.
     *
     * @param string $message
     * @return FormValidator
     */
    public function digits($message = null) {
        $this->set_rule(__FUNCTION__, function($value) {
                    return (ctype_digit((string) $value));
                }, $message);
        return $this;
    }

    /**
     * Field must be a number greater than or equal to X.
     *
     * @param numeric $value
     * @param bool $include Whether to include limit value.
     * @param string $message
     * @return FormValidator
     */
    public function min($value, $include = TRUE, $message = null) {
        $this->set_rule(__FUNCTION__, function($string, $args) {
                    $value = $args[0];

                    if ($args[1] == TRUE) { // Include limit or not
                        return ((float) $value > (float) $string) ? FALSE : TRUE;
                    } else {
                        return ((float) $value >= (float) $string) ? FALSE : TRUE;
                    }
                }, $message, array($value, $include));
        return $this;
    }

    /**
     * Field must be a number greater than or equal to X.
     *
     * @param numeric $value
     * @param bool $include Whether to include limit value.
     * @param string $message
     * @return FormValidator
     */
    public function max($value, $include = TRUE, $message = null) {
        $this->set_rule(__FUNCTION__, function($string, $args) {
                    $value = $args[0];

                    if ($args[1] == TRUE) { // Include limit or not
                        return ((float) $value < (float) $string) ? FALSE : TRUE;
                    } else {
                        return ((float) $value <= (float) $string) ? FALSE : TRUE;
                    }
                }, $message, array($value, $include));
        return $this;
    }

    /**
     * Field must be a number between X and Y.
     *
     * @param numeric $min
     * @param numeric $max
     * @param bool $include Whether to include limit value.
     * @param string $message
     * @return FormValidator
     */
    public function between($min, $max, $include = TRUE, $message = null) {
        $message = self::getDefaultMessage(__FUNCTION__, array($min, $max));

        $this->min($min, $include, $message)->max($max, $include, $message);
        return $this;
    }

    /**
     * Field has to be greater than or equal to X characters long.
     *
     * @param int $len
     * @param string $message
     * @return FormValidator
     */
    public function minlength($len, $message = null) {
        $this->set_rule(__FUNCTION__, function($string, $args) {
                    return (strlen(trim($string)) < $args[0]) ? FALSE : TRUE;
                }, $message, array($len));
        return $this;
    }

    /**
     * Field has to be less than or equal to X characters long.
     *
     * @param int $len
     * @param string $message
     * @return FormValidator
     */
    public function maxlength($len, $message = null) {
        $this->set_rule(__FUNCTION__, function($string, $args) {
                    return (strlen(trim($string)) > $args[0]) ? FALSE : TRUE;
                }, $message, array($len));
        return $this;
    }

    /**
     * Field has to be X characters long.
     *
     * @param int $len
     * @param string $message
     * @return FormValidator
     */
    public function length($len, $message = null) {
        $this->set_rule(__FUNCTION__, function($string, $args) {
                    return (strlen(trim($string)) == $args[0]) ? TRUE : FALSE;
                }, $message, array($len));
        return $this;
    }

    /**
     * Field is the same as another one (ex. for password comparison).
     *
     * @param string $field
     * @param string $label
     * @param string $message
     * @return FormValidator
     */
    public function matches($field, $label, $message = null) {
        $this->set_rule(__FUNCTION__, function($string, $args) {
                    return ((string) $args[0] == (string) $string) ? TRUE : FALSE;
                }, $message, array($this->getval($field), $label));
        return $this;
    }

    /**
     * Field is different from another one.
     *
     * @param string $field
     * @param string $label
     * @param string $message
     * @return FormValidator
     */
    public function notmatches($field, $label, $message = null) {
        $this->set_rule(__FUNCTION__, function($string, $args) {
                    return ((string) $args[0] == (string) $string) ? FALSE : TRUE;
                }, $message, array($this->getval($field), $label));
        return $this;
    }

    /**
     * Field has to be valid IP address.
     *
     * @param string $message
     * @return FormValidator
     */
    public function ip($message = null) {
        $this->set_rule(__FUNCTION__, function($string) {
                    return (strlen(trim($string)) === 0 || filter_var($string, FILTER_VALIDATE_IP)) ? TRUE : FALSE;
                }, $message);
        return $this;
    }

    /**
     * Field has to be valid internet address.
     *
     * @param string $message
     * @return FormValidator
     */
    public function url($message = null) {
        $this->set_rule(__FUNCTION__, function($string) {
                    return (strlen(trim($string)) === 0 || filter_var($string, FILTER_VALIDATE_URL)) ? TRUE : FALSE;
                }, $message);
        return $this;
    }

    /**
     * Date format.
     *
     * @return string
     */
    private static function getDefaultDateFormat() {
        return 'd/m/Y';
    }

    /**
     * Field has to be a valid date.
     *
     * @param string $message
     * @return FormValidator
     */
    public function date($format = null, $separator = null, $message = null) {
        if (empty($format)) {
            $format = self::getDefaultDateFormat();
        }

        $this->set_rule(__FUNCTION__, function($string, $args) {
                    if (strlen(trim($string)) === 0) {
                        return TRUE;
                    }

                    $separator = $args[1];
                    $dt = (is_null($separator)) ? preg_split('/[-\.\/ ]/', $string) : explode($separator, $string);

                    if ((count($dt) != 3) || !is_numeric($dt[2]) || !is_numeric($dt[1]) || !is_numeric($dt[0])) {
                        return FALSE;
                    }

                    $dateToCheck = array();
                    $format = explode('/', $args[0]);
                    foreach ($format as $i => $f) {
                        switch ($f) {
                            case 'Y':
                                $dateToCheck[2] = $dt[$i];
                                break;

                            case 'm':
                                $dateToCheck[1] = $dt[$i];
                                break;

                            case 'd':
                                $dateToCheck[0] = $dt[$i];
                                break;
                        }
                    }

                    return (checkdate($dateToCheck[1], $dateToCheck[0], $dateToCheck[2]) === FALSE) ? FALSE : TRUE;
                }, $message, array($format, $separator));
        return $this;
    }

    /**
     * Field has to be a date later than or equal to X.
     *
     * @param string $message
     * @return FormValidator
     */
    public function mindate($date = 0, $format = null, $message = null) {
        if (empty($format)) {
            $format = self::getDefaultDateFormat();
        }
        if (is_numeric($date)) {
            $date = new DateTime($date . ' days'); // Days difference from today
        } else {
            $fieldValue = $this->getval($date);
            $date = ($fieldValue == FALSE) ? $date : $fieldValue;

            $date = DateTime::createFromFormat($format, $date);
        }

        $this->set_rule(__FUNCTION__, function($string, $args) {
                    $format = $args[1];
                    $limitDate = $args[0];

                    return ($limitDate > DateTime::createFromFormat($format, $string)) ? FALSE : TRUE;
                }, $message, array($date, $format));
        return $this;
    }

    /**
     * Field has to be a date later than or equal to X.
     *
     * @param string|integer $date Limit date.
     * @param string $format Date format.
     * @param string $message
     * @return FormValidator
     */
    public function maxdate($date = 0, $format = null, $message = null) {
        if (empty($format)) {
            $format = self::getDefaultDateFormat();
        }
        if (is_numeric($date)) {
            $date = new DateTime($date . ' days'); // Days difference from today
        } else {
            $fieldValue = $this->getval($date);
            $date = ($fieldValue == FALSE) ? $date : $fieldValue;

            $date = DateTime::createFromFormat($format, $date);
        }

        $this->set_rule(__FUNCTION__, function($string, $args) {
                    $format = $args[1];
                    $limitDate = $args[0];

                    return ($limitDate < DateTime::createFromFormat($format, $string)) ? FALSE : TRUE;
                }, $message, array($date, $format));
        return $this;
    }

    /**
     * Field has to be a valid credit card number format.
     *
     * @see https://github.com/funkatron/inspekt/blob/master/Inspekt.php
     * @param string $message
     * @return FormValidator
     */
    public function ccnum($message = null) {
        $this->set_rule(__FUNCTION__, function($value) {
                    $value = str_replace(' ', '', $value);
                    $length = strlen($value);

                    if ($length < 13 || $length > 19) {
                        return FALSE;
                    }

                    $sum = 0;
                    $weight = 2;

                    for ($i = $length - 2; $i >= 0; $i--) {
                        $digit = $weight * $value[$i];
                        $sum += floor($digit / 10) + $digit % 10;
                        $weight = $weight % 2 + 1;
                    }

                    $mod = (10 - $sum % 10) % 10;

                    return ($mod == $value[$length - 1]);
                }, $message);
        return $this;
    }

    /**
     * Field has to be a date later than or equal to X.
     *
     * @param string|integer $date Limit date.
     * @param string $format Date format.
     * @param string $message
     * @return FormValidator
     */
    public function oneof($allowed, $message = null) {
        if (is_string($allowed)) {
            $allowed = explode(',', $allowed);
        }

        $this->set_rule(__FUNCTION__, function($string, $args) {
                    return in_array($string, $args[0]);
                }, $message, array($allowed));
        return $this;
    }

    // --------------- END [ADD NEW RULE FUNCTIONS ABOVE THIS LINE] ------------

    /**
     * callback
     * @param string $name
     * @param mixed $function
     * @param string $message
     * @return FormValidator
     */
    public function callback($name, $function, $message='') {
        if (is_callable($function)) {
            // set rule and function
            $this->set_rule($name, $function, $message);
        } elseif (is_string($function) && preg_match($function, 'callback') !== FALSE) {
            // we can parse this as a regexp. set rule function accordingly.
            $this->set_rule($name, function($value) use ($function) {
                        return ( preg_match($function, $value) ) ? TRUE : FALSE;
                    }, $message);
        } else {
            // just set a rule function to check equality.
            $this->set_rule($name, function($value) use ( $function) {
                        return ( (string) $value === (string) $function ) ? TRUE : FALSE;
                    }, $message);
        }
        return $this;
    }

    /**
     * validate
     * @param string $key
     * @param string $label
     * @return bool
     */
    public function validate($key, $label = '') {
        // set up field name for error message
        $this->fields[$key] = (empty($label)) ? 'Field with the name of "' . $key . '"' : $label;

        // try each rule function
        foreach ($this->rules as $rule => $is_true) {
            if ($is_true) {
                $function = $this->functions[$rule];
                $args = $this->arguments[$rule]; // Arguments of rule

                $valid = (empty($args)) ? $function($this->getval($key)) : $function($this->getval($key), $args);
                if ($valid === FALSE) {
                    $this->register_error($rule, $key);

                    $this->rules = array();  // reset rules
                    return FALSE;
                }
            }
        }

        // reset rules
        $this->rules = array();
        return TRUE;
    }

    /**
     * Whether errors have been found.
     *
     * @return bool
     */
    public function hasErrors() {
        return (count($this->errors) > 0) ? TRUE : FALSE;
    }

    /**
     * Get specific error.
     *
     * @param string $field
     * @return string
     */
    public function getError($field) {
        return $this->errors[$field];
    }

    /**
     * Get all errors.
     *
     * @return array
     */
    public function getAllErrors($keys = true) {
        return ($keys == true) ? $this->errors : array_values($this->errors);
    }

    /**
     * getval
     * @param string $key
     * @return mixed
     */
    private function getval($key) {
        return (isset($this->data[$key])) ? $this->data[$key] : FALSE;
    }

    /**
     * Register error.
     *
     * @param string $rule
     * @param string $key
     * @param string $message
     */
    private function register_error($rule, $key, $message = null) {
        if (empty($message)) {
            $message = $this->messages[$rule];
        }

        $this->errors[$key] = sprintf($message, $this->fields[$key]);
    }

    /**
     * Set rule.
     *
     * @param string $rule
     * @param closure $function
     * @param string $message
     */
    private function set_rule($rule, $function, $message = '', $args = array()) {
        if (!array_key_exists($rule, $this->rules)) {
            $this->rules[$rule] = TRUE;
            if (!array_key_exists($rule, $this->functions)) {
                if (!is_callable($function)) {
                    die('Invalid function for rule: ' . $rule);
                }
                $this->functions[$rule] = $function;
            }
            $this->arguments[$rule] = $args; // Specific arguments for rule

            $this->messages[$rule] = (empty($message)) ? self::getDefaultMessage($rule, $args) : $message;
        }
    }

    /**
     * Get default error message.
     *
     * @param string $key
     * @return string
     */
    private static function getDefaultMessage($rule, $args = null) {
        switch ($rule) {
            case 'email':
                $message = '%s is an invalid email address.';
                break;

            case 'ip':
                $message = '%s is an invalid IP address.';
                break;

            case 'url':
                $message = '%s is an invalid url.';
                break;

            case 'required':
                $message = '%s is required.';
                break;

            case 'float':
                $message = '%s must consist of numbers only.';
                break;

            case 'integer':
                $message = '%s must consist of integer value.';
                break;

            case 'digits':
                $message = '%s must consist only of digits.';
                break;

            case 'min':
                $message = '%s must be greater than ';
                if ($args[1] == TRUE) {
                    $message .= 'or equal to ';
                }
                $message .= $args[0] . '.';
                break;

            case 'max':
                $message = '%s must be less than ';
                if ($args[1] == TRUE) {
                    $message .= 'or equal to ';
                }
                $message .= $args[0] . '.';
                break;

            case 'between':
                $message = '%s must be between ' . $args[0] . ' and ' . $args[1] . '.';
                break;

            case 'minlength':
                $message = '%s must be at least ' . $args[0] . ' characters or longer.';
                break;

            case 'maxlength':
                $message = '%s must be no longer than ' . $args[0] . ' characters.';
                break;

            case 'length':
                $message = '%s must be exactly ' . $args[0] . ' characters in length.';
                break;

            case 'matches':
                $message = '%s must match ' . $args[1] . '.';
                break;

            case 'notmatches':
                $message = '%s must not match ' . $args[1] . '.';
                break;

            case 'date':
                $message = '%s δεν είναι έγκυρη';
                break;

            case 'mindate':
                $message = '%s pprepei na einai megaluteri apo ' . $args[0]->format($args[1]) . '.';
                break;

            case 'maxdate':
                $message = '%s pprepei na einai mikroteri apo ' . $args[0]->format($args[1]) . '.';
                break;

            case 'oneof':
                $message = '%s must be one of ' . implode(', ', $args[0]) . '.';
                break;

            case 'ccnum':
                $message = '%s must be a valid credit card number.';
                break;

            default:
                $message = '%s has an error.';
                break;
        }

        return $message;
    }

}
?>



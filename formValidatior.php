<?php

/**
 * Author: Kıvanç Ağaoğlu
 * Web: https://kivancagaoglu.com
 * Mail: info@kivancagaoglu.com
 * Skype: kivancagaoglu
 *
 */

class formValidatior
{
    /**
     * Default error messages by rules
     * @var array
     */
    protected $defaultErrors = [
        'required' => '{label} zorunlu alandır',
        'min_lenght' => "{label} en az {value} karakter olabilir",
        'max_lenght' => '{label} en fazla {value} karakter olabilir',
        'alphanumeric' => '{label} yalnızca sayı ve harf içerebilir',
        'alpha' => '{label} yalnızca harf içerebilir',
        'regex' => '{label} desene uymuyor',
        'valid_email' => '{value} geçersiz eposta adresi',
        'numeric' => '{label} yalnızca sayısal değer alabilir',
        'is_equal' => '{label} eşleşmiyor',
    ];

    /**
     * Form values to validate
     * @var array
     */
    public $formData;

    /**
     * All errors messages after validation
     * @var array
     */
    public $errors = [];

    /**
     * @var string default div class for displaying errors
     */
    public $divClass = 'alert alert-custom alert-outline-danger fade show mb-5';

    public function validate()
    {
        $status = true;
        foreach ($this->formData as $key => $item) {
            $rules = explode('|', $item['rules']);
            foreach ($rules as $rule) {
                if ($this->hasRuleValue($rule)) {
                    preg_match('/(.*?)\[(.*?)\]/', $rule, $match);
                    $rule = $match[1];
                    $parameter = $match[2];
                    if(is_array($parameter)){
                        foreach ($parameter as $param){
                            if (!$this->validateRule($rule, $item['value'], $param)) {
                                $status = false;
                                $this->setError($rule, $key, $param);
                                break;
                            }
                        }
                    }else{
                        if (!$this->validateRule($rule, $item['value'], $parameter)) {
                            $status = false;
                            $this->setError($rule, $key, $parameter);
                        }
                    }
                } else {
                    if (!$this->validateRule($rule, $item['value'])) {
                        $status = false;
                        $this->setError($rule, $key, $item['value']);
                    }
                }
            }
        }
        return $status;
    }

    public function validateRule($ruleName, $value = null, $ruleParam = null): bool
    {
        if ($ruleName == 'max_lenght') {
            return $this->$ruleName($value, $ruleParam);
        }
        if ($ruleName == 'min_lenght') {
            return $this->$ruleName($value, $ruleParam);
        }
        if ($ruleName == 'alphanumeric') {
            return $this->$ruleName($value);
        }
        if ($ruleName == 'alpha') {
            return $this->$ruleName($value);
        }
        if ($ruleName == 'regex') {
            return $this->$ruleName($value, $ruleParam);
        }
        if ($ruleName == 'valid_email') {
            return $this->$ruleName($value);
        }
        if ($ruleName == 'is_equal') {
            return $this->$ruleName($value, $ruleParam);
        }
        if ($ruleName == 'required') {
            return $this->$ruleName($value);
        }
        if ($ruleName == 'numeric') {
            return $this->$ruleName($value);
        }
        throw new \RuntimeException("Undefined rule", 0);
    }

    public function min_lenght($value, $lenght): bool
    {
        return !(mb_strlen($value) < $lenght);
    }

    public function max_lenght($value, $lenght): bool
    {
        return !(mb_strlen($value) > $lenght);
    }

    public function required($value): bool
    {
        if(is_array($value)){
            $value = array_filter($value);
            if(empty($value)){
                return false;
            }
            return true;
        }
        return !(!$value or trim($value) == '');
    }

    public function alphanumeric($value): bool
    {
        return ctype_alnum($value);
    }

    public function alpha($value): bool
    {
        return ctype_alpha($value);
    }

    public function regex($value, $pattern)
    {
        return preg_match($pattern, $value);
    }

    public function valid_email($value)
    {
        return FILTER_VAR($value, FILTER_VALIDATE_EMAIL);
    }

    public function is_equal($value, $equal): bool
    {
        return $value == $this->formData[$equal]['value'];
    }

    public function numeric($value): bool
    {
        return is_numeric($value);
    }

    public function returnData() : array{
        $data = [];
        foreach ($this->formData as $key => $item){
            $data[$key] = $item['value'];
        }
        return $data;
    }

    public function hasRuleValue($rule): bool
    {

        return preg_match('/\[.*?\]/', $rule);

    }

    public function setError($rule, $key = null, $param = null)
    {
        $find = [
            '{label}',
            '{value}'
        ];
        if($this->formData[$key]['errors'][$rule]){
            $error = $this->formData[$key]['errors'][$rule];
        }else{
            $error = $this->defaultErrors[$rule];
        }
        $replace['label'] = $this->formData[$key]['label'] ?? null;
        $replace['value'] = $param ?? null;
        if ($replace['label'] or $replace['value']) {
            $this->errors[$key][$rule] = str_replace($find, $replace, $error);
        } else {
            $this->errors[$key][$rule] = $error;
        }
    }

    public function getError($key)
    {
        if (!$this->errors[$key]) {
            return null;
        }
        $string = '<div class="' . $this->$divClass . '" role="alert">
    <div class="alert-icon"><i class="flaticon-warning"></i></div>';
        $string .= ' <div class="alert-text">' . implode('<br>', $this->errors[$key]) . '</div>';
        $string .= '<div class="alert-close">
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true"><i class="ki ki-close"></i></span>
        </button>
    </div>
</div>';
        return $string;
    }

    public function getErrors(){
        return empty($this->errors) ? false : $this->errors;
    }

    public function displayErrors(): string
    {
        if($this->errors){
            $string = '<div class="' . $this->divClass . '" role="alert">
    <div class="alert-icon"><i class="flaticon-warning"></i></div>' . PHP_EOL;

            $string .= '<div class="alert-text">';

            $string .= "\t<ul>" . PHP_EOL;

            foreach ($this->errors as $errors) {
                foreach ($errors as $error) {
                    $string .= "\t\t<li>" . $error . '</li>' . PHP_EOL;
                }
            }

            $string .= "\t</ul>" . PHP_EOL;

            $string .= '</div></div>' . PHP_EOL;

            echo $string;

        }

        return '';

    }

}

<?php

namespace Wulff\util;

class Validator
{
    const REQUIRED = 'required';
    const MIN_LENGTH = 'minLength';
    const MAX_LENGTH = 'maxLength';
    const NUMERIC = 'numeric';
    const MIN_VALUE = 'minValue';
    const MAX_VALUE = 'maxValue';
    const ALPHA = 'alpha';
    const TEXT = 'text';

    private $_errors = [];
    private $_data = [];

    public function validate($data = null, $rules = [])
    {
        // check if body is present
        if (!isset($data)) {
            // body not present, add error and return
            $this->addError('body', 'body required');
            return;
        }

        // check if any fields in data not in rules
        foreach ($data as $key => $value) {
            if (!array_key_exists($key, $rules)) {
                // key does not exists in rules, remove
                unset($data[$key]);
            }
        }

        // update data
        $this->_data = $data;

        // check if any missing fields in data
        foreach ($rules as $key => $value) {
            if (!array_key_exists($key, $data)) {
                // data field missing
                $this->addError($key, strtolower($key) . ' required');
            }
        }

        // check if fields conform to the rules
        foreach ($data as $item => $item_value) {
            if (key_exists($item, $rules)) {
                foreach ($rules[$item] as $rule => $rule_value) {

                    if (is_int($rule))
                        $rule = $rule_value;

                    switch ($rule) {
                        case self::REQUIRED:
                            if (empty($item_value) && $rule_value) {
                                $this->addError($item, strtolower($item) . ' required');
                            }
                            break;

                        case self::NUMERIC:
                            if (!ctype_digit($item_value) && $rule_value) {
                                $this->addError($item, strtolower($item) . ' should be numeric');
                            }
                            break;

                        case self::TEXT:
                            if (!is_string($item_value) && $rule_value) {
                                $this->addError($item, strtolower($item) . ' should be a text');
                            }
                            break;

                        case self::ALPHA:
                            if (!ctype_alpha($item_value) && $rule_value) {
                                $this->addError($item, strtolower($item) . ' should be alphabetic characters');
                            }
                            break;

                        case self::MIN_LENGTH:
                            if (strlen($item_value) < $rule_value) {
                                $this->addError($item, strtolower($item) . ' should be minimum ' . $rule_value . ' characters');
                            }
                            break;

                        case self::MAX_LENGTH:
                            if (strlen($item_value) > $rule_value) {
                                $this->addError($item, strtolower($item) . ' should be maximum ' . $rule_value . ' characters');
                            }
                            break;

                        case self::MIN_VALUE:
                            if ($item_value < $rule_value) {
                                $this->addError($item, strtolower($item) . 'should be minimum ' . $rule_value);
                            }
                            break;

                        case self::MAX_VALUE:
                            if ($item_value > $rule_value) {
                                $this->addError($item, strtolower($item) . 'should be maximum ' . $rule_value);
                            }
                            break;
                    }
                }
            }
        }
    }

    private function addError($item, $error)
    {
        $this->_errors[$item][] = $error;
    }

    // returns an nested array of fields with array of errors
    public function error()
    {
        if (empty($this->_errors)) return false;
        return $this->_errors;
    }

    // returns the updated array of data, elements not in the rules, will be removed
    public function data()
    {
        return $this->_data;
    }
}
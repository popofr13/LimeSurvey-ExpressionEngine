<?php

namespace org\limesurvey\ExpressionEngine\base;

use \org\limesurvey\ExpressionEngine;
use \org\limesurvey\ExpressionEngine\core;

// Elementary Expression Types
/**
 * An expression container for a single value (integer, float, string)
 */
class ConstantExpression implements  core\IExpression
{
    private $value;
    
    public function __construct($value)
    {
        $this->value = $value;
    }
    
    public function evaluate()
    {
        return $this->value;
    }
	
	public function toPostfix() {
		return $this->value;
	}
	
    public function toInfix() {
        return $this->value;
    }
}

/**
 * Expression for variables. Variables are late-bound - resolved
 * during evaluation (pulled from the ExpressionEngine)
 */
class VariableExpression implements core\IExpression
{
	private $name = "";
	public function __construct($var_name) {
		$this->name = $var_name;
	}
	public function evaluate() {
		return ExpressionEngine\ExpressionEngine::getVar($this->name);
	}
	
	public function toPostfix() {
		return "@" . $this->name;
	}
	
	public function toInfix() {
        return "{" . $this->name . "}";
    }
}
?>

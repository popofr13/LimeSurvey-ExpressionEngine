<?php

namespace org\limesurvey\ExpressionEngine\math;

use \org\limesurvey\ExpressionEngine\core;

/**
 * Binary addition
 */
class AddExpression extends core\ABinaryExpression
{
    public function evaluate()
    {
        return $this->left->evaluate() + $this->right->evaluate();
    }
	
	public function getLiteral() { return "+"; }
}

/**
 * Binary subtraction
 */
class SubtractExpression extends core\ABinaryExpression
{
    public function evaluate()
    {
        return $this->left->evaluate() - $this->right->evaluate();
    }
	
	public function getLiteral() { return "-"; }
}

/**
 * Binary multiplication
 */
class MultiplyExpression extends core\ABinaryExpression
{
    public function evaluate()
    {
        return $this->left->evaluate() * $this->right->evaluate();
    }
	
	public function getLiteral() { return "*"; }
}

/**
 * Binary division
 */
class DivideExpression extends core\ABinaryExpression
{
    public function evaluate()
    {
        return $this->left->evaluate() / $this->right->evaluate();
    }
	
	public function getLiteral() { return "/"; }
}


// Advanced Maths

/** 
 * Square
 */
class SquareExpression extends core\AUnaryExpression
{
	public function evaluate()
    {
        $exp = $this->exp->evaluate();
        return $exp * $exp; 
    }
	
	public function getLiteral() { return "sqr"; }
}

/** 
 * Power
 */
class PowerExpression extends core\ABinaryExpression
{
    public function evaluate()
    {
        return pow($this->left->evaluate(),$this->right->evaluate());
    }
    
    public function getLiteral() { return "^"; }
}


// need register some functions
\org\limesurvey\ExpressionEngine\ExpressionEngine::register('sqr', 'org\limesurvey\ExpressionEngine\math\SquareExpression');
\org\limesurvey\ExpressionEngine\ExpressionEngine::register('pow', 'org\limesurvey\ExpressionEngine\math\PowerExpression');

?>

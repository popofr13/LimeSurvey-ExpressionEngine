<?php

namespace org\limesurvey\ExpressionEngine\logic;

use \org\limesurvey\ExpressionEngine\core;

/**
 * Less-than-or-equal comparison
 * @todo handle strings
 */
class LeqExpression extends core\ABinaryExpression
{
    public function evaluate()
    {
        $left = $this->left->evaluate();
        $right = $this->right->evaluate();
        if (is_string($left) || is_string($right)) {
            return (strcmp($left, $right) <= 0);
        } else {
            return ($left <= $right);            
        }
    }
	
	public function getLiteral() { return "&lt;="; }
}

/**
 * Less-than comparison
 * @todo handle strings
 */
class LtExpression extends core\ABinaryExpression
{
    public function evaluate()
    {
        $left = $this->left->evaluate();
        $right = $this->right->evaluate();
        if (is_string($left) || is_string($right)) {
            return (strcmp($left, $right) < 0);
        } else {
            return ($left < $right);            
        }
    }
	
	public function getLiteral() { return "&lt;"; }
}

/**
 * Greater-than-or-equal comparison
 * @todo handle strings
 */
class GeqExpression extends core\ABinaryExpression
{
    public function evaluate()
    {
        $left = $this->left->evaluate();
        $right = $this->right->evaluate();
        if (is_string($left) || is_string($right)) {
            return (strcmp($left, $right) >= 0);
        } else {
            return ($left >= $right);            
        }
    }
	
	public function getLiteral() { return "&gt;="; }
}

/**
 * Greater-than comparison
 */
class GtExpression extends core\ABinaryExpression
{
    public function evaluate()
    {
    	$left = $this->left->evaluate();
		$right = $this->right->evaluate();
    	if (is_string($left) || is_string($right)) {
    		return (strcmp($left, $right) > 0);
    	} else {
            return ($left > $right);    		
    	}
    }
	
	public function getLiteral() { return "&gt;"; }
}

/**
 * Equality comparison. Handles strings defaulty by PHP behaviour
 */
class EqExpression extends core\ABinaryExpression
{
    public function evaluate()
    {
        return ($this->left->evaluate() == $this->right->evaluate());
    }
	
	public function getLiteral() { return "=="; }
}

/**
 * Not-equal comparison. Handles strings defaulty by PHP behaviour
 */
class NeqExpression extends core\ABinaryExpression
{
    public function evaluate()
    {
        return ($this->left->evaluate() != $this->right->evaluate());
    }
	
	public function getLiteral() { return "!="; }
}

/* 
 * LOGIC FUNCTIONS
 */
class AndExpression extends core\ABinaryExpression
{
    public function evaluate() {
    	return ($this->left->evaluate() && $this->right->evaluate());
    }
	
	public function getLiteral() { return "&&"; }
}

class OrExpression extends core\ABinaryExpression
{
    public function evaluate() {
        return ($this->left->evaluate() || $this->right->evaluate());
    }
    
    public function getLiteral() { return "||"; }
}

class NotExpression extends core\AUnaryExpression
{
    public function evaluate() {
        return (!$this->exp->evaluate());
    }
    
    public function getLiteral() { return "!"; }
}
?>

<?php

namespace org\limesurvey\ExpressionEngine\core;

// Base Abstract Classes and Interfaces
/**
 * Expression Interface
 */
interface IExpression 
{
    public function evaluate();     /** Evaluates the expression */
	public function toPostfix();    /** converts object into RPN/Postfix notation e.g. "5 6 + 7 *" */
	public function toInfix();      /** converts object into Normal/Infix notation e.g. "(5 + 6) * 7" */  
}

/**
 * Abstract base class for binary operators.
 */
abstract class ABinaryExpression implements IExpression
{
    protected $left;   /** Left Expression*/
    protected $right;  /** Left Expression*/
    /**
     * Constructs the expression. Either pass left and right expressions, or
     * pass a stack and the expressions will be retrieved.
     */
    public function __construct($a, $b = null) 
    {
        if ($a instanceof Stack) {
            $this->right = $a->read();
            $a->pop();
            $this->left = $a->read();
            $a->pop();
        } else {
            $this->left = $a;
            $this->right = $b;
        }
    }
	
	/** 
	 * Returns operator symbol/literal. Descendants must implement this to
	 * simplify the implementation of toInfix() and toPostfix() functions 
	 * @return 
	 */
	abstract function getLiteral(); 
	
	public function toPostfix() 
	{
        return $this->left->toPostfix() . " " . $this->right->toPostfix() . " " . $this->getLiteral(); 
	}
	
	public function toInfix()
	{
		return "(" . $this->left->toInfix() . $this->getLiteral() . $this->right->toInfix() . ")";
	}
}

/**
 * Same as ABinaryExpression, but toInfix uses a prefix function notation.
 */
abstract class ABinaryFunctionExpression extends ABinaryExpression {
	public function toInfix() {
		return $this->getLiteral() . "(" . $this->left->toInfix() . "," . $this->right->toInfix() . ")"; 
	}
}

/**
 * 
 */
abstract class AUnaryExpression implements IExpression
{
    protected $exp;   /** First expression */
    /**
     * Constructs the expression. Either pass an expression, or pass a stack.
     */
    public function __construct($a) 
    {
        if ($a instanceof Stack) {
            $this->exp = $a->read();
            $a->pop();            
        } else {
            $this->exp = $a;
        }
    }
	
	/** 
     * Returns operator symbol/literal. Descendants must implement this to
     * simplify the implementation of toInfix() and toPostfix() functions 
     * @return 
     */
    abstract function getLiteral(); 
	
	public function toPostfix() 
	{
		return $this->exp->toPostfix() . " " . $this->getLiteral();
	}
	
	public function toInfix()
    {
        return $this->getLiteral() . "(" . $this->exp->toInfix() . ")";
    }
}

/** 
 *  Simple stack implementation,
 *  for stacking Expressions (e.g. useful during Postfix parsing)
 *  Also used widely in constructors of Expressions.
 */

class Stack {
    private $stack = array();
    private $count = 0;    /** Write pointer, equivalent to current number of items */
    
    /** Pushes one item onto the stack */
    public function push($val) 
    {
        $this->stack[$this->count] = $val;
        $this->count++;
    }
    
    /** Pops specified number of items from the stack */
    public function pop($items = 1) 
    {
        $this->count -= $items;
    }
    
    /** Returns last value */
    public function read() 
    {
        if ($this->count == 0) 
        {
            return null;
        } else
        { 
            return $this->stack[$this->count - 1];
        }
    }
	
	/** returns true if it is empty */
	public function isEmpty() {
	   return ($this->count == 0);
	}
}

?>
<?php
/**
 * For more details: http://www.docs.limesurvey.org/tiki-index.php?page=Expression+Engine+for+Conditions
 * 
 * 'Postfix' and 'RPN' are used interchangeably.
 * 'Infix notation', 'Normal notation' are used interchangeably.
 * 
 * Example code:
 * 
 *   Evalute the following expression with 1 variable: (6+7)*11 < a
 *   
 *      $exp = ExpressionEngine::create("6 7 + 11 * @a lt");
 *      ExpressionEngine::bind(array("a" => 143));
 *      var_dump($exp->evaluate());                  // prints 'boolean(true)'
 *
 *      ExpressionEngine::bind(array("a" => 145));   
 *      var_dump($exp->evaluate());                  // prints 'boolean(false)'
 * 
 * Requires PHP 5.3.0+
 *
 * @since 13.9.2009
 * @author macduy (adapted for PHP 5.3 (namespaces) by Sébastien Porati @popo13fr)
 */

namespace org\limesurvey\ExpressionEngine;

// Include abstract base class(es) and interface(s), helper classes and 
// declaration of basic library Expression atoms
require "core/lib.core.php";
require "base/lib.base.php";
require "math/lib.math.php";
require "logic/lib.logic.php";

// TODO: write an autolog

// Enum constants for infix->postfix parser
define('ST_DEFAULT', 0);
define('ST_POSSIBLE_OPERATOR', 1);
define('ST_VARIABLE', 2);
define('ST_FUNCTION', 3);
define('ST_NUMBER', 4);
define('ST_STRING', 5);
define('ST_POSSIBLY_TERMINATED_STRING', 6);

// TODO: Un-globalize the constants

/**
 * Main ExpressionEngine. 
 * 
 * DOCUMENTATION TO BE WRITTEN
 */
class ExpressionEngine
{
	/** Currently bound variables */
	private static $vars = array();


	
	/** Registered custom expression literals, mapping to the respective class name */
	private static $literals = array();
	
	/** Custom defined string tokenizer. Tokenizes on ' ' (space) character. The in-built PHP function strtok has
	 * undesired behaviour since version PHP 5.2.0 and is thus unsuitable for our purposes.
	 */
	function parsetok($string = null, $del = null) {
	    static $pos = 0;
	    static $str = '';
		static $strlen = 0;
		// set new string and reset position
		if ($del != null) 
		{
			$str = $string;
			$pos = 0;
			$strlen = strlen($str);
		} else {
			$del = $string;
		}
		
		if ($pos > $strlen) return FALSE;
		
	    $newpos = strpos($str, $del, $pos);
	    if ($newpos === FALSE)
		{
			if ($pos < $strlen) 
			{
				// use this new newpos and continue happily
				$newpos = $strlen;
			} else {
				// we reached the end of string
                return FALSE;				
			}
	    }
		
        $token = substr($str, $pos, $newpos - $pos);
        $pos = $newpos + 1;
        return $token;
    }
	
	
    /**
     * From a string expression in Postfix notation (also called RPN), constructs an IExpression 
     * (or more precisely one of its decendants), that can be evaluated against a list of input values 
     * ('variables').
     * 
     * To evaluate the expression, call evaluate() on the returned object. To use 'variables',
     * bind an array in the format ('variable_name' => 'value') using ExpressionEngine::bind($vars)
     * prior to calling evaluate. Note this means that all expression share the same variable space.
     *
     * @return a decendant of IExpression
     */
    public static function create($infix_string)
    {
    	// prepare a stack
		$stack = new core\Stack();
		
        // split input string into 'tokens'
        $token = self::parsetok($infix_string, ' ');
		$expr = null;
        while ($token !== false)
        {
			// skip if token is empty 
			if ($token == '') {
				$token = self::parsetok(' ');
				continue;
			}
			
			// ... otherwise process
            if (is_numeric($token))
            {
            	// recognize numeric constant value
				if (strpos($token, '.') === FALSE) {
                    $expr = new base\ConstantExpression(intval($token));
				} else {
				    $expr = new base\ConstantExpression(floatval($token));
			    }
            } else if ($token[0] == '@') {
            	// recognize a variable
				$expr = new base\VariableExpression(substr($token, 1));
			} else if ($token[0] == '"'){
				// recognize a string
				// Strings are delimited using double quotes. To use a double quote inside the string, type it twice in a row.
				//
				// An complete token is recognized by having an odd number of consecutive double quotes at the end
				$string_buffer = $token;
				if (preg_match('/^"(""|[^"])*"$/', $token) != 1) {					
					// search for the end of the string
					$string_buffer = $token;
					do  
					{
						$token = self::parsetok(' '); 
						$string_buffer .= ' ';
						$string_buffer .= $token;
					} while ($token !== FALSE && (preg_match('/^(""|[^"])*"$/', $token) != 1));
					
					if ($token === FALSE) {
						// TODO: throw Error because we overrun the input string
					}
				}	
				// replace all double double quotes with single double quotes ;-)
                // also strip the delimiter quotes from both ends
                $expr = new base\ConstantExpression(str_replace('""', '"', substr($string_buffer,1,-1)));
			} else
            {
                // library expressions and their recognized string literals
                switch ($token)
                {
                    case '+':
						$expr = new math\AddExpression($stack);
						break;
					case '-':
                        $expr = new math\SubtractExpression($stack);
                        break;
					case '*':
						$expr = new math\MultiplyExpression($stack);
						break;
					case '^':
						$expr = new math\PowerExpression($stack);
						break;
					case '/':
                        $expr = new math\DivideExpression($stack);
                        break;
					case '<=':
					case 'leq':
                        $expr = new logic\LeqExpression($stack);
                        break;
					case '>=':
                    case 'geq':
                        $expr = new logic\GeqExpression($stack);
                        break;
					case '<':
                    case 'lt':
                        $expr = new logic\LtExpression($stack);
                        break;
					case '>':
                    case 'gt':
                        $expr = new logic\GtExpression($stack);
                        break;
					case 'eq':
                    case '==':
                        $expr = new logic\EqExpression($stack);
                        break;
					case 'neq':
                    case '!=':
                        $expr = new logic\NeqExpression($stack);
                        break;
					case 'and':
					case '&&':
                        $expr = new logic\AndExpression($stack);
                        break;
					case '||':
					case 'or':
						$expr = new logic\OrExpression($stack);
                        break;
					case 'not':
					case '!':
						$expr = new logic\NotExpression($stack);
						break;
				    // constants
					case 'TRUE':
						$expr = new base\ConstantExpression(true);
						break;
					case 'FALSE':
						$expr = new base\ConstantExpression(false);
						break;
				    default:
						// $token is none of the default literals
						// search through registered custom expression literals
						if (isset(self::$literals[$token])) {
							$className = self::$literals[$token];
							$expr = new $className($stack);
						} else {
							// TODO: handle the case of $token not being recognized: ignore? Throw error?
							// error, $token not recognized
                            throw new \Exception("Token $token unrecognized");
						}
                }
            }

            // push the created expression onto the stack
			if ($expr != null) 
			{
			    $stack->push($expr);
				$expr = null;
			}
			            
            // retrieve next token
            $token = self::parsetok(' ');
        }
		
		// read off the top of the stack - that is our final expression
		return $stack->read();
    }
    
	/**
	 * Converts a string expression in infix notation to postfix. If there are no errors, it then creates
	 * the expression from the infix notation string
	 */
	public static function createFromInfix($infix_string) {
		try 
		{
		    $postfix_string = self::fromInfixToPostfix($infix_string);
		    return self::create($postfix_string);
		} catch (Exception $e)
		{
		  // TODO: Catch block
	    }
	}
	
	/**
	 * Binds variables for evaluation of expressions containing variables.
	 * 
	 * @param object $vars Variables array
	 * @todo some proper memory management
	 * @todo sophisticated variable handling and management
	 */
	public static function bind($vars) {
		self::$vars = $vars;
	}
	
	/**
	 * @return current value of a variable.
	 * @todo error handling on non-bound variable
	 */
        
	public static function getVar($name) {
        if (isset(self::$vars[$name])) {
            return self::$vars[$name];			
		} else {
		    // TODO: throw error?
			return null;
		}
	}
	
	/**
	 * Registers a string as a literal 'hook' for an expression during parsing. E.g.
	 * registering 'test' will cause the parser to look for TestExpression and create it
	 * if the class exists. You can also specify your own class name.
	 */
	public static function register($literal, $className = null) {
		// construct default class name
		if ($className == null) {
            $className = self::getClassName($literal);			
		}
	      
		// register only if literal does not exist yet and class name exists
		if (class_exists($className) && !isset(self::$literals[$literal]))
		{
			self::$literals[$literal] = $className;
		} else {
			// TODO: error
		}
	}
	
	/**
	 * Returns expected class name from given expression literal 
	 * 
	 * @param object $literal
	 * @return 
	 */
	private static function getClassName($literal) {
		return ucfirst($literal) . "Expression";
	}
	
	/**
	 * Returns operator precedence. Higher index means higher precedence.
	 * Used during conversion from infix to postfix
	 * @return integer precendence
	 */
	private static function getPrecedence($op) 
	{
		switch ($op) 
		{
			case '(':
				return 0;
		    case '!':
				return 5;
		    case '<':
			case '>':
			case '<=':
            case '>=':
			case '==':
			case '!=':
				return 10;
			case '+':
		    case '-':
				return 30;
			case '*':
			case '/':
			    return 50;
		    case '^':
				return 60;
		}
	}
	
	/**
	 * Converts a string from infix notation to postfix notation.
	 * Uses Dijkstra's Shunting Yard Algorithm, notes on:
	 *     http://en.wikipedia.org/wiki/Shunting_yard_algorithm
	 */
	private static function fromInfixToPostfix($string) {
		// predefined stuff
		$operator_symbols = array('+','-','*','/','&','|','!','<','>','=','^');     // characters that denote a start of an predefined operator
		$left_assoc = array('+','-','*','/');                                   // List of operators considered left-associative
		
		// prepare variables
		$length = strlen($string);
		$char = '';           // currently read character
		$token = '';          // parsed token
		$status = ST_DEFAULT; // current status
		$output = "";         // output buffer
		$stack = new core\Stack(); // String stack to store operator and function literals
		$advance = true;      // set to false to prevent advancement to the next character
		
		// read the string char by char
		$i = 0;
		while ($i < $length) 
		{
			// read in character
			$char = $string[$i];
			$advance = true;
			
			switch ($status) {
				case ST_POSSIBLY_TERMINATED_STRING:
					if ($char == '"') {
						$token .= '"';
						$status = ST_STRING;
					} else {
						// string terminated
						$output .= ' ' . $token;
						$token = null;
						$advance = false;
						$status = ST_DEFAULT;
					}
					break;
                case ST_STRING:
					$token .= $char;
                    if ($char == '"') {
                        $status = ST_POSSIBLY_TERMINATED_STRING;
                    }
                    break;
				case ST_DEFAULT:
					if (is_numeric($char)) {
						// a number
						$token = $char;
						$status = ST_NUMBER;
					} else if ($char == '"') {
						$token = '"';
                        $status = ST_STRING;
					} else if ($char == '(') {
						// left bracket operator
						$stack->push('(');
					} else if ($char == ')') {
						// TODO: function case
						
						// pop everything off until left bracket, or stack underflow (parenthesis mismatch)
						$symb = $stack->read();
						while (!$stack->isEmpty() && $symb != '(') {
							$output .= " " . $symb;
							$stack->pop();
							$symb = $stack->read();
						}
						if ($stack->isEmpty()) {
							// TODO: Error - parenthesis mismatch
						} else {
							$stack->pop(); // pop off the left bracket
							// if the top of the stack is now a function, output it and pop it off
							$symb = $stack->read();
							if (isset(self::$literals[$symb])) {
							    $output .= " " . $symb;
							    $stack->pop();
							}
						}
					} else if ($char == '{') {
						// variable start
						$status = ST_VARIABLE;
					} else if ($char == ','){
						// function argument separator
						// TODO: stack underflow
						$symb = $stack->read();
                        while (!$stack->isEmpty() && $symb != '(') {
                            $output .= " " . $symb;
                            $stack->pop();
                            $symb = $stack->read();
                        }
					} else if (in_array($char, $operator_symbols)) {
						// recognized as a possible operator
						$token = $char;
						$status = ST_POSSIBLE_OPERATOR;
					} else if ($char != ' ') {
						// function start
						$token = $char;
						$status = ST_FUNCTION;
					}
					break;
				case ST_NUMBER:
					if (is_numeric($char) || $char == '.')
					{
						$token .= $char;
					} else 
					{
						$output .= " " . $token;
						$status = ST_DEFAULT;
						$advance = false;
			        }
					break;
				case ST_POSSIBLE_OPERATOR:
					if (!in_array($char, $operator_symbols)) {
						// TODO: should check against operator existence
						// operator finished
						$o1 = $token;
						$o1_prec = self::getPrecedence($o1);
						
						// From wiki: while there is an operator, o2, at the top of the stack (this excludes left parenthesis),
						// and either:
						//    -o1 is left-assoc and its precedence is less than or equal to that of o2, 
						// or -o1 is right-assoc and its precedence is less than that of o2,
						//       pop o2 off the stack, onto the output queue..
						// in this implementation, only + - * / are considered left-assoc
						$o2 = $stack->read();
						while (!$stack->isEmpty() && 
						   (in_array($o1, $left_assoc) && $o1_prec <= self::getPrecedence($o2)) ||
						   ($o1_prec < self::getPrecedence($o2))
					    ) 
						{
							$output .= " " . $o2;
							$stack->pop();
							$o2 = $stack->read();
						}
						
						// ... then push $o1 onto the stack
						$stack->push($o1);
						$token = "";
						$status = ST_DEFAULT;
						$advance = false;
					} else {
						// keep parsing as a possible operator
						$token .= $char;
					}
					break;
				case ST_VARIABLE:
					if ($char == '}') 
					{
						// end of variable name, output the variable in postfix form
						$output .= " @" . $token;
						$token = "";
						$status = ST_DEFAULT;
					} else {
						$token .= $char;
					}
					break;
				case ST_FUNCTION:
					if ($char == '(') {
						// check against existing function literals
						if (isset(self::$literals[$token])) 
						{
							$stack->push($token);
							$token = "";
							$advance = false;
						} else 
						{
							// TODO: ERROR: function does not exists
							
						}
						$status = ST_DEFAULT;
						$token = "";
					} else {
						$token .= $char;
					}
					break;
			}
			
			// next character
			if ($advance) $i++;
			
		}
		// output any remaining tokens
		// (might have to restrict this to ST_NUMBER being on)
		$output .= " " . $token;
		
		// pop everything off the stack, unless we hit a left bracket, which is a parenthesis mismatch error
		// TODO: implement this brackeete mismatch detection
		while (!$stack->isEmpty()) {
			$output .= " " . $stack->read();
			$stack->pop();
		}
		
		// done
		return ltrim($output);
	}
}

?>

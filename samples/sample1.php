<?php

    // http://docs.limesurvey.org/tiki-index.php?page=Expression+Engine+for+Conditions

    include '../lib/ExpressionEngine.class.php';

    use org\limesurvey\ExpressionEngine;

    use org\limesurvey\ExpressionEngine\base\ConstantExpression as ConstantExpression;
    use org\limesurvey\ExpressionEngine\logic\LeqExpression     as LeqExpression;
    use org\limesurvey\ExpressionEngine\math\AddExpression      as AddExpression;

    $exp = ExpressionEngine\ExpressionEngine::create("1 1 + 2 ==");
    $result = $exp->evaluate();
    var_dump($result);

    $exp = new LeqExpression(new ConstantExpression(12), new AddExpression(new ConstantExpression(10), new ConstantExpression(3)));
    $result = $exp->evaluate();
    var_dump($result);

    var_dump($exp->toPostfix());
    var_dump($exp->toInfix());

    $exp = ExpressionEngine\ExpressionEngine::create("6 7 + 11 * @a sqr <");

    ExpressionEngine\ExpressionEngine::bind(array("a" => 12));
    $result = $exp->evaluate();
    var_dump($result);  // Returns TRUE

    ExpressionEngine\ExpressionEngine::bind(array("a" => 11));
    $result = $exp->evaluate();
    var_dump($result); // Returns FALSE
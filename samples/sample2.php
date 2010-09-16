<?php

    include '../lib/ExpressionEngine.class.php';

    use org\limesurvey\ExpressionEngine;

    use org\limesurvey\ExpressionEngine\base\ConstantExpression as ConstantExpression;
    use org\limesurvey\ExpressionEngine\base\VariableExpression as VariableExpression;
    use org\limesurvey\ExpressionEngine\logic\AndExpression as AndExpression;
    use org\limesurvey\ExpressionEngine\logic\EqExpression as EqExpression;
    use org\limesurvey\ExpressionEngine\logic\OrExpression as OrExpression;

    // Simple : by domain

    $exp = new EqExpression(
        new ConstantExpression('dreamnex.com'),
        new VariableExpression('domain')
    );

    ExpressionEngine\ExpressionEngine::bind(array("domain" => 'dreamnex.com'));
    $result = $exp->evaluate();
    var_dump($exp->toInfix());
    var_dump($result);

    ExpressionEngine\ExpressionEngine::bind(array("domain" => 'dreamnex.fr'));
    $result = $exp->evaluate();
    var_dump($exp->toInfix());
    var_dump($result);

    // By domain AND user

    $domainExpression = new EqExpression(
        new ConstantExpression('dreamnex.com'),
        new VariableExpression('domain')
    );

    $userExpression = new EqExpression(
        new ConstantExpression('contact'),
        new VariableExpression('user')
    );

    $exp = new AndExpression($domainExpression, $userExpression);

    ExpressionEngine\ExpressionEngine::bind(array("domain" => 'dreamnex.com', 'user' => 'contact'));
    $result = $exp->evaluate();
    var_dump($exp->toInfix());
    var_dump($result);

    ExpressionEngine\ExpressionEngine::bind(array("domain" => 'dreamnex.com', 'user' => 'support'));
    $result = $exp->evaluate();
    var_dump($exp->toInfix());
    var_dump($result);

    // By user (multiple choice)
    $userValues = array('contact', 'support', 'abuse', 'webmaster');

    $userExpression1 = new EqExpression(
        new ConstantExpression($userValues[0]),
        new VariableExpression('user')
    );
    
    $userExpression2 = new EqExpression(
        new ConstantExpression($userValues[1]),
        new VariableExpression('user')
    );

    $exp = new OrExpression($userExpression1, $userExpression2);
    foreach(array_slice($userValues, 2) as $user) {
        $userExpression = new EqExpression(
            new ConstantExpression($user),
            new VariableExpression('user')
        );

        $exp = new OrExpression($exp, $userExpression);
    }

    var_dump($exp->toInfix());

    ExpressionEngine\ExpressionEngine::bind(array('user' => 'contact'));
    $result = $exp->evaluate();
    var_dump($result);

    ExpressionEngine\ExpressionEngine::bind(array('user' => 'support'));
    $result = $exp->evaluate();
    var_dump($result);

    ExpressionEngine\ExpressionEngine::bind(array('user' => 'abuse'));
    $result = $exp->evaluate();
    var_dump($result);

    ExpressionEngine\ExpressionEngine::bind(array('user' => 'webmaster'));
    $result = $exp->evaluate();
    var_dump($result);

    ExpressionEngine\ExpressionEngine::bind(array('user' => 'blabla'));
    $result = $exp->evaluate();
    var_dump($result);
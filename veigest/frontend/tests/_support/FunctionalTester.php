<?php

namespace frontend\tests;

/**
 * Inherited Methods
 * @method void wantToTest($text)
 * @method void wantTo($text)
 * @method void execute($callable)
 * @method void expectTo($prediction)
 * @method void verify($prediction)
 * @method void amGoingTo($argumentation)
 * @method void am($role)
 * @method void lookForwardTo($achieveValue)
 * @method void comment($description)
 * @method \Codeception\Lib\Friend haveFriend($name, $actorClass = NULL)
 *
 * @SuppressWarnings(PHPMD)
 */
class FunctionalTester extends \Codeception\Actor
{
    use _generated\FunctionalTesterActions;

    /**
     * Login como um utilizador específico
     * 
     * @param string $username
     * @param string $password
     */
    public function login($username, $password)
    {
        $this->amOnPage('/site/login');
        $this->fillField('LoginForm[username]', $username);
        $this->fillField('LoginForm[password]', $password);
        $this->click('login-button');
    }

    /**
     * Login como manager (acesso ao frontend)
     */
    public function loginAsManager()
    {
        $this->login('manager', 'manager123');
    }

    /**
     * Login como driver
     */
    public function loginAsDriver()
    {
        $this->login('driver1', 'driver123');
    }
}

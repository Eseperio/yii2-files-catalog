<?php


/**
 * Inherited Methods
 * @method void wantToTest($text)
 * @method void wantTo($text)
 * @method void execute($callable)
 * @method void expectTo($prediction)
 * @method void expect($prediction)
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
    * Define custom actions here
    */

    public function changeToTenant($tenantId)
    {
        // get current route =
        $currentRoute = \Yii::$app->controller->route;
        $this->startFollowingRedirects();
        $this->amOnPage(['/tenant/tenant/toggle', 'id' => $tenantId]);
        $this->stopFollowingRedirects();
    }
}

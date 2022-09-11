<?php

namespace blog\system\event\listener;

use wcf\system\event\listener\IParameterizedEventListener;
use wcf\system\user\jcoins\UserJCoinsStatementHandler;

/**
 * JCoins create blog listener.
 *
 * @author        2017-2022 Darkwood.Design
 * @license        Commercial Darkwood.Design License <https://darkwood.design/lizenz/>
 * @package        de.wcflabs.blog.jcoins
 */
class JCoinsCreateBlogListener implements IParameterizedEventListener
{
    /**
     * @inheritdoc
     */
    public function execute($eventObj, $className, $eventName, array &$parameters)
    {
        if (!MODULE_JCOINS) {
            return;
        }

        switch ($eventObj->getActionName()) {
            case 'create':
                $returnValues = $eventObj->getReturnValues();
                $blog = $returnValues['returnValues'];

                // user required
                if (!$blog->userID) {
                    return;
                }

                UserJCoinsStatementHandler::getInstance()->create('de.wcflabs.jcoins.statement.blog', $blog, ['userID' => $blog->userID]);
                break;

            case 'delete':
                foreach ($eventObj->getObjects() as $object) {
                    // user required
                    if (!$object->userID) {
                        continue;
                    }

                    UserJCoinsStatementHandler::getInstance()->revoke('de.wcflabs.jcoins.statement.blog', $object->getDecoratedObject(), ['userID' => $object->userID]);
                }
                break;
        }
    }
}

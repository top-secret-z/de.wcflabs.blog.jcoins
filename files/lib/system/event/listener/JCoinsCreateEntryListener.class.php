<?php

namespace blog\system\event\listener;

use wcf\system\event\listener\IParameterizedEventListener;
use wcf\system\user\jcoins\UserJCoinsStatementHandler;

/**
 * JCoins create blog entry listener.
 *
 * @author        2017-2022 Darkwood.Design
 * @license        Commercial Darkwood.Design License <https://darkwood.design/lizenz/>
 * @package        de.wcflabs.blog.jcoins
 */
class JCoinsCreateEntryListener implements IParameterizedEventListener
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
            case 'triggerPublication':
                foreach ($eventObj->getObjects() as $object) {
                    if ($object->isPublished && $object->userID) {
                        UserJCoinsStatementHandler::getInstance()->create('de.wcflabs.jcoins.statement.entry', $object->getDecoratedObject());
                    }
                }
                break;

            // 'enable' calls triggerPublication

            case 'disable':
                foreach ($eventObj->getObjects() as $object) {
                    if ($object->isPublished && !$object->isDeleted && $object->userID) {
                        UserJCoinsStatementHandler::getInstance()->revoke('de.wcflabs.jcoins.statement.entry', $object->getDecoratedObject());
                    }
                }
                break;

            case 'trash':
                foreach ($eventObj->getObjects() as $object) {
                    if ($object->isPublished && !$object->isDisabled && $object->userID) {
                        UserJCoinsStatementHandler::getInstance()->revoke('de.wcflabs.jcoins.statement.entry', $object->getDecoratedObject());
                    }
                }
                break;

            case 'delete':
                // if blog with entries is deleted
                foreach ($eventObj->getObjects() as $object) {
                    if (!$object->isDeleted && $object->isPublished && !$object->isDisabled && $object->userID) {
                        UserJCoinsStatementHandler::getInstance()->revoke('de.wcflabs.jcoins.statement.entry', $object->getDecoratedObject());
                    }
                }
                break;

            case 'restore':
                foreach ($eventObj->getObjects() as $object) {
                    if ($object->isPublished && !$object->isDisabled && $object->userID) {
                        UserJCoinsStatementHandler::getInstance()->create('de.wcflabs.jcoins.statement.entry', $object->getDecoratedObject());
                    }
                }
                break;

            case 'update':
                foreach ($eventObj->getObjects() as $object) {
                    if (!$object->isPublished && isset($actionParameters['data']['isPublished']) && !empty($actionParameters['data']['isPublished'])) {
                        UserJCoinsStatementHandler::getInstance()->create('de.wcflabs.jcoins.statement.entry', $object->getDecoratedObject());
                    }
                }
                break;
        }
    }
}

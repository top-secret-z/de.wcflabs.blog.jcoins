<?php

/*
 * Copyright by Udo Zaydowicz.
 * Modified by SoftCreatR.dev.
 *
 * License: http://opensource.org/licenses/lgpl-license.php
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public
 * License as published by the Free Software Foundation; either
 * version 3 of the License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
 * Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public License
 * along with this program; if not, write to the Free Software Foundation,
 * Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
 */
namespace blog\system\event\listener;

use wcf\system\event\listener\IParameterizedEventListener;
use wcf\system\user\jcoins\UserJCoinsStatementHandler;

/**
 * JCoins create blog entry listener.
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

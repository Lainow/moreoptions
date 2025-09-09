<?php

/**
 * -------------------------------------------------------------------------
 * More Options plugin for GLPI
 * -------------------------------------------------------------------------
 *
 * LICENSE
 *
 * This file is part of More Options.
 *
 * More Options is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
 * (at your option) any later version.
 *
 * More Options is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with More Options. If not, see <http://www.gnu.org/licenses/>.
 * -------------------------------------------------------------------------
 * @copyright Copyright (C) 2022-2024 by More Options plugin team.
 * @copyright Copyright (C) 2022-2024 by Cloud Inventory plugin team.
 * @license   GPLv3 https://www.gnu.org/licenses/gpl-3.0.html
 * @link      https://gitlab.teclib.com/glpi-network/cancelsend/
 * @link      https://gitlab.teclib.com/glpi-network/cloudinventory/
 * -------------------------------------------------------------------------
 */

namespace GlpiPlugin\Moreoptions\Tests\Units;

use GlpiPlugin\Moreoptions\Config;
use PHPUnit\Framework\TestCase;

class ConfigTest extends TestCase
{
    /**
     * @test
     */
    public function testTaskMandatoryField()
    {
        $conf = Config::getCurrentConfig();
        $this->assertNotNull($conf);

        $config = new Config();
        $input = [
            'id'                        => $conf->getID(),
            'is_active'                 => 1,
            'entities_id'               => 0,
            'mandatory_task_category'   => 1,
            'mandatory_task_duration'   => 1,
            'mandatory_task_user'       => 1,
            'mandatory_task_group'      => 1,
        ];

        $this->assertTrue($config->update($input));

        $conf = Config::getCurrentConfig();
        $this->assertNotNull($conf);

        //Create a ticket
        $ticket = new \Ticket();
        $ticket->add(
            [
                'content'          => 'Test ticket',
                'content'       => 'Test content',
            ]
        );
        $this->assertNotFalse($ticket->getID());

        //Create a task without mandatory fields (Expected to fail)
        $task = new \TicketTask();
        $result = $task->add(
            [
                'tickets_id'    => $ticket->getID(),
                'content'          => 'Test task',
            ]
        );
        $this->assertFalse($result);

        // Create category
        $category = new \TaskCategory();
        $result = $category->add(
            [
                'content' => 'Test category',
            ]
        );
        $this->assertNotFalse($result);

        //Create a task with mandatory fields (Expected to succeed)
        $task = new \TicketTask();
        $result = $task->add(
            [
                'tickets_id'    => $ticket->getID(),
                'content'          => 'Test task',
                'taskcategories_id' => 1,
                'users_id_tech'      => 1,
                'groups_id_tech'     => 1,
                'actiontime'         => 300,
            ]
        );
        $this->assertNotFalse($result);

        // Create task without user (Expected to fail)
        $task = new \TicketTask();
        $result = $task->add(
            [
                'tickets_id'    => $ticket->getID(),
                'content'          => 'Test task without user',
                'taskcategories_id' => 1,
                'groups_id_tech'     => 1,
                'actiontime'         => 300,
            ]
        );
        $this->assertFalse($result);

        // Create task without group (Expected to fail)
        $task = new \TicketTask();
        $result = $task->add(
            [
                'tickets_id'    => $ticket->getID(),
                'content'          => 'Test task without group',
                'taskcategories_id' => 1,
                'users_id_tech'      => 1,
                'actiontime'         => 300,
            ]
        );
        $this->assertFalse($result);

        // Create task without duration (Expected to fail)
        $task = new \TicketTask();
        $result = $task->add(
            [
                'tickets_id'    => $ticket->getID(),
                'content'          => 'Test task without duration',
                'taskcategories_id' => 1,
                'users_id_tech'      => 1,
                'groups_id_tech'     => 1,
            ]
        );
        $this->assertFalse($result);

        // Create task without category (Expected to fail)
        $task = new \TicketTask();
        $result = $task->add(
            [
                'tickets_id'    => $ticket->getID(),
                'content'          => 'Test task without category',
                'users_id_tech'      => 1,
                'groups_id_tech'     => 1,
                'actiontime'         => 300,
            ]
        );
        $this->assertFalse($result);

        //Check if we have only 1 task
        $tasks = new \TicketTask();
        $tasks = count($tasks->find(['tickets_id' => $ticket->getID()]));
        $this->assertEquals(1, $tasks);
    }
}

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
                'name'          => 'Test ticket task mandatory fields',
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
                'state'             => \Planning::TODO
            ]
        );
        $this->assertFalse($result);

        // Create category
        $category = new \TaskCategory();
        $result = $category->add(
            [
                'name' => 'Test category',
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
                'state'             => \Planning::TODO
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
                'state'             => \Planning::TODO
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
                'state'             => \Planning::TODO
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
                'state'             => \Planning::TODO
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
                'state'             => \Planning::TODO
            ]
        );
        $this->assertFalse($result);

        //Check if we have only 1 task
        $tasks = new \TicketTask();
        $tasks = count($tasks->find(['tickets_id' => $ticket->getID()]));
        $this->assertEquals(1, $tasks);

        // Reset config
        $input = [
            'id'                        => $conf->getID(),
            'mandatory_task_category'   => 0,
            'mandatory_task_duration'   => 0,
            'mandatory_task_user'       => 0,
            'mandatory_task_group'      => 0,
        ];
        $this->assertTrue($config->update($input));
    }

    /**
     * @test
     */
    public function testTicketMandatoryFieldsBeforeClose()
    {
        $conf = Config::getCurrentConfig();
        $this->assertNotNull($conf);

        $config = new Config();
        $input = [
            'id'                        => $conf->getID(),
            'is_active'                 => 1,
            'entities_id'               => 0,
            'require_technician_to_close_ticket'   => 1,
            'require_technicians_group_to_close_ticket'   => 1,
            'require_category_to_close_ticket'       => 1,
            'require_location_to_close_ticket'      => 1,
            //require_solution_to_close_ticket       => 1,
        ];

        $this->assertTrue($config->update($input));

        $conf = Config::getCurrentConfig();
        $this->assertNotNull($conf);

        //Create a ticket without mandatory fields (Expected to succeed)
        $ticket = new \Ticket();
        $tid = $ticket->add(
            [
                'name'          => 'Test ticket close',
                'content'       => 'Test content',
            ]
        );
        $this->assertGreaterThan(0, $tid);

        $ticket = new \Ticket();
        $this->assertTrue($ticket->update(
            [
                'id'                => $tid,
                'status'            => \Ticket::SOLVED,
                'itilcategories_id' => 1, // Default category
            ]
        ));

        // Create group
        $group = new \Group();
        $gid = $group->add(
            [
                'name' => 'Test group close ticket',
            ]
        );
        $this->assertNotFalse($gid);

        // Close the ticket without mandatory fields (Expected to fail)
        $ticket = new \Ticket();
        $this->assertTrue($ticket->getFromDB($tid));
        $result = $ticket->update(
            [
                'id'          => $tid,
                'status'      => \Ticket::CLOSED,
            ]
        );
        $this->assertFalse($result);

        // Create category
        $category = new \ITILCategory();
        $cid = $category->add(
            [
                'name' => 'Test category close ticket',
            ]
        );
        $this->assertNotFalse($cid);

        // Create location
        $location = new \Location();
        $lid = $location->add(
            [
                'name' => 'Test location close ticket',
            ]
        );
        $this->assertNotFalse($lid);

        // Add actors to the ticket
        $gticket = new \Group_Ticket();
        $this->assertNotFalse($gticket->add(
            [
                'tickets_id' => $tid,
                'groups_id'  => $gid,
                'type'       => \Group_Ticket::ASSIGN,
            ]
        ));

        $user = new \User();
        $this->assertTrue($user->getFromDBByCrit(
            [
                'name' => 'glpi',
            ]
        ));

        $uticket = new \Ticket_User();
        $this->assertNotFalse($uticket->add(
            [
                'tickets_id' => $tid,
                'users_id'   => $user->getID(),
                'type'       => \Ticket_User::ASSIGN,
            ]
        ));

        // Close the ticket without mandatory fields (Expected to fail)
        $ticket = new \Ticket();
        $this->assertFalse($ticket->update(
            [
                'id'          => $tid,
                'status'      => \Ticket::CLOSED,
            ]
        ));
        $this->assertFalse($result);

        // Close the ticket with all mandatory fields (Expected to succeed)
        $ticket = new \Ticket();
        $result = $ticket->update(
            [
                'id'                => $tid,
                'name'              => 'Test ticket close - updated',
            ]
        );
        $this->assertNotFalse($result);

        // Reset config
        $input = [
            'id'                                            => $conf->getID(),
            'require_technician_to_close_ticket'            => 0,
            'require_technicians_group_to_close_ticket'     => 0,
            'require_category_to_close_ticket'              => 0,
            'require_location_to_close_ticket'              => 0,
        ];
        $this->assertTrue($config->update($input));
    }

    /**
     * @test
     */
    public function testTakeTheRequesterGroup()
    {
        $conf = Config::getCurrentConfig();
        $this->assertNotNull($conf);

        $config = new Config();
        $input = [
            'id'                               => $conf->getID(),
            'is_active'                        => 1,
            'entities_id'                      => 0,
            'take_requester_group_ticket'      => 2, // All
        ];

        $this->assertTrue($config->update($input));

        $conf = Config::getCurrentConfig();
        $this->assertNotNull($conf);

        // Create two groups
        $group1 = new \Group();
        $result = $group1->add(
            [
                'name' => 'Test group 1',
            ]
        );
        $this->assertNotFalse($result);

        $group2 = new \Group();
        $result = $group2->add(
            [
                'name' => 'Test group 2',
            ]
        );
        $this->assertNotFalse($result);

        // Get the user glpi
        $user = new \User();
        $this->assertTrue($user->getFromDBByCrit(
            [
                'name' => 'glpi',
            ]
        ));

        // Assign the user to the group
        $group_user = new \Group_User();
        $result = $group_user->add(
            [
                'groups_id' => $group1->getID(),
                'users_id'  => $user->getID(),
            ]
        );
        $this->assertNotFalse($result);

        $result = $group_user->add(
            [
                'groups_id' => $group2->getID(),
                'users_id'  => $user->getID(),
            ]
        );
        $this->assertNotFalse($result);

        //Create a ticket
        $ticket = new \Ticket();
        $tid= $ticket->add(
            [
                'name'          => 'Test ticket requester group',
                'content'       => 'Test content',
            ]
        );
        $this->assertGreaterThan(0, $tid);

        $uticket = new \Ticket_User();
        $this->assertNotFalse($uticket->add(
            [
                'tickets_id' => $tid,
                'users_id'   => $user->getID(),
                'type'       => \Ticket_User::REQUESTER,
            ]
        ));

        // Check if the group of the requester is in the actors
        $ticket_group = new \Group_Ticket();
        $groups = $ticket_group->find(['tickets_id' => $ticket->getID()]);
        $this->assertCount(2, $groups);

        $config = new Config();
        $input = [
            'id'                               => $conf->getID(),
            'is_active'                        => 1,
            'entities_id'                      => 0,
            'take_requester_group_ticket'     => 1, // Default
        ];

        $this->assertTrue($config->update($input));

        $conf = Config::getCurrentConfig();
        $this->assertNotNull($conf);

        //Create a ticket
        $ticket = new \Ticket();
        $tid = $ticket->add(
            [
                'name'          => 'Test ticket requester group - 2',
                'content'       => 'Test content',
            ]
        );
        $this->assertNotFalse($ticket->getID());

        //Add default group to the user
        $user2 = new \User();
        $this->assertTrue($user2->update(
            [
                'id'   => $user->getID(),
                'groups_id' => $group1->getID(),
            ]
        ));

        $uticket = new \Ticket_User();
        $this->assertNotFalse($uticket->add(
            [
                'tickets_id' => $tid,
                'users_id'   => $user2->getID(),
                'type'       => \Ticket_User::REQUESTER,
            ]
        ));

        // Check if the group of the requester is in the actors
        $ticket_group = new \Group_Ticket();
        $groups = $ticket_group->find(['tickets_id' => $tid]);
        $this->assertCount(1, $groups);

        // Reset config
        $input = [
            'id'                               => $conf->getID(),
            'is_active'                        => 1,
            'entities_id'                      => 0,
            'take_requester_group_ticket'      => 0, // Default
        ];
        $this->assertTrue($config->update($input));
    }

    /**
     * @test
     */
    public function testTakeTheTechnicianGroup()
    {
        $conf = Config::getCurrentConfig();
        $this->assertNotNull($conf);

        $config = new Config();
        $input = [
            'id'                               => $conf->getID(),
            'is_active'                        => 1,
            'entities_id'                      => 0,
            'take_technician_group_ticket'     => 2, // All
        ];

        $this->assertTrue($config->update($input));

        $conf = Config::getCurrentConfig();
        $this->assertNotNull($conf);

        // Create two groups
        $group1 = new \Group();
        $result = $group1->add(
            [
                'name' => 'Test group 1',
            ]
        );
        $this->assertNotFalse($result);

        $group2 = new \Group();
        $result = $group2->add(
            [
                'name' => 'Test group 2',
            ]
        );
        $this->assertNotFalse($result);

        // Get the user tech
        $user = new \User();
        $this->assertTrue($user->getFromDBByCrit(
            [
                'name' => 'tech',
            ]
        ));

        // Assign the user to the group
        $group_user = new \Group_User();
        $result = $group_user->add(
            [
                'groups_id' => $group1->getID(),
                'users_id'  => $user->getID(),
            ]
        );
        $this->assertNotFalse($result);

        $result = $group_user->add(
            [
                'groups_id' => $group2->getID(),
                'users_id'  => $user->getID(),
            ]
        );
        $this->assertNotFalse($result);

        //Create a ticket
        $ticket = new \Ticket();
        $tid = $ticket->add(
            [
                'name'          => 'Test ticket',
                'content'       => 'Test content',
            ]
        );
        $this->assertNotFalse($ticket->getID());

        $uticket = new \Ticket_User();
        $this->assertNotFalse($uticket->add(
            [
                'tickets_id' => $tid,
                'users_id'   => $user->getID(),
                'type'       => \Ticket_User::ASSIGN,
            ]
        ));

        // Check if the group of the requester is in the actors
        $ticket_group = new \Group_Ticket();
        $groups = $ticket_group->find(['tickets_id' => $ticket->getID()]);
        $this->assertCount(2, $groups);

        $config = new Config();
        $input = [
            'id'                               => $conf->getID(),
            'is_active'                        => 1,
            'entities_id'                      => 0,
            'take_technician_group_ticket'      => 1, // Default
        ];
        $this->assertTrue($config->update($input));

        $conf = Config::getCurrentConfig();
        $this->assertNotNull($conf);

        //Create a ticket
        $ticket = new \Ticket();
        $tid = $ticket->add(
            [
                'name'          => 'Test ticket tech group - 2',
                'content'       => 'Test content',
            ]
        );
        $this->assertNotFalse($ticket->getID());

        //Add default group to the user
        $user2 = new \User();
        $this->assertTrue($user2->update(
            [
                'id'   => $user->getID(),
                'groups_id' => $group1->getID(),
            ]
        ));

        $uticket = new \Ticket_User();
        $this->assertNotFalse($uticket->add(
            [
                'tickets_id' => $tid,
                'users_id'   => $user2->getID(),
                'type'       => \Ticket_User::ASSIGN,
            ]
        ));

        // Check if the group of the requester is in the actors
        $ticket_group = new \Group_Ticket();
        $groups = $ticket_group->find(['tickets_id' => $ticket->getID()]);
        $this->assertCount(1, $groups);
    }
}

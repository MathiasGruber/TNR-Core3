<?php
/* ============== LICENSE INFO START ==============
 * 2005 - 2016 Studie-Tech ApS, All Rights Reserved
 *
 * This file is part of the project www.TheNinja-RPG.com.
 * Dissemination of this information or reproduction of this material
 * is strictly forbidden unless prior written permission is obtained
 * from Studie-Tech ApS.
 * ============== LICENSE INFO END ============== */
?>
<?php
require_once(Data::$absSvrPath.'/global_libs/Quests/QuestsControl.php');
require_once(Data::$absSvrPath.'/global_libs/Quests/QuestContainer.php');

class QuestJournal
{
    public function __construct()
    {
        //getting quest control started if need be
        if(!isset($GLOBALS['QuestsControl']))
            $GLOBALS['QuestsControl'] = new QuestsControl();//temp

        //getting quests
        $quests = $GLOBALS['QuestsControl']->QuestsData->quests;
        $display_main_page = true;

        if(isset($_GET['questing_mode']))
            $GLOBALS['QuestsControl']->questingMode($_GET['questing_mode']);

        //checking to see if a dialog needs the users attention
        if($GLOBALS['userdata'][0]['dialog'] != '')
        {
            $display_main_page = $GLOBALS['QuestsControl']->showDialog();
        }

        //checking to see if the action start is being called
        else if(isset($_GET['start']) //checking to see if start is set
        && isset($quests[$_GET['start']]) //checking to see that this user has the needed quest
        && $quests[$_GET['start']]->status == QuestContainer::$known //checking to see that that status of the quest is known
        && $GLOBALS['QuestsControl']->canStart($_GET['start'], true)) //checking to see that the quest can be started currently
        {
            $display_main_page = $GLOBALS['QuestsControl']->tryStart($_GET['start']);
        }

        //checking to see if the action turn in is being called
        else if(isset($_GET['turn_in']) //checking to see if turn in is set
        && isset($quests[$_GET['turn_in']]) //checking to see that this user has the needed quest
        && $quests[$_GET['turn_in']]->status == QuestContainer::$completed //checking to see that that status of the quest is completed
        && $GLOBALS['QuestsControl']->canTurnIn($_GET['turn_in'], true)) //checking to see that the quest can be started currently
        {
            $display_main_page = $GLOBALS['QuestsControl']->tryTurnIn($_GET['turn_in']);
        }

        //checking to see if the action quit is being called
        else if(isset($_GET['quit']) //checking to see if stop is set
        && isset($quests[$_GET['quit']]) //checking to see that this user has the needed quest
        && $quests[$_GET['quit']]->status == QuestContainer::$active) //checking to see that the status of this quest is active
        {
            $quest = $quests[$_GET['quit']];

            if(isset($_POST['Submit']))
            {
                $display_main_page = $GLOBALS['QuestsControl']->tryQuit($_GET['quit']);
            }
            else if ($quest->failable && $quest->hard_fail)
            {
                $GLOBALS['page']->Confirm("QUITING THIS QUEST WILL FAIL IT PERMANENTLY!!! <br/> (You will not be able to start this quest again.) <br/> Are you sure you want to quit this quest!? <br/><br/><br/>", 'Quit: '.$quest->name, 'Yes');
                $display_main_page = false;
            }
            else if ($quest->failable)
            {
                $GLOBALS['page']->Confirm("Quiting this quest will fail it. <br/> Are you sure you want to quit this quest? <br/> This will clear your current progress.", 'Quit: '.$quest->name, 'Yes');
                $display_main_page = false;
            }
            else
            {
                $GLOBALS['page']->Confirm("Are you sure you want to quit this quest? <br/> This will clear your current progress.", 'Quit: '.$quest->name, 'Yes');
                $display_main_page = false;
            }
        }


        //checking to see if the action forget is being called
        else if(isset($_GET['forget']) //checking to see if stop is set
        && isset($quests[$_GET['forget']]) //checking to see that this user has the needed quest
        &&  (
                $quests[$_GET['forget']]->status == QuestContainer::$known //checking to see that the status of this quest is known
                ||                                                         //or completed
                $quests[$_GET['forget']]->status == QuestContainer::$completed //checking to see that the status of this quest is completed
            )
        && $quests[$_GET['forget']]->forgettable)
        {
            $quest = $quests[$_GET['forget']];

            if(isset($_POST['Submit']))
            {
                $display_main_page = $GLOBALS['QuestsControl']->tryForget($_GET['forget']);
            }
            else
            {
                $GLOBALS['page']->Confirm("Are you sure you want to forget this quest? All data on this quest will be lost. <br/><br/><br/>", 'Forget: '.$quest->name, 'Yes');
                $display_main_page = false;
            }
        }

        else if(isset($_GET['track']) && isset($quests[$_GET['track']]))
        {
            $GLOBALS['QuestsControl']->QuestsData->trackQuest($_GET['track']);

            if($GLOBALS['QuestsControl']->QuestsData->quests[$_GET['track']]->track == '1')
                $GLOBALS['QuestsControl']->QuestsData->quests[$_GET['track']]->track = '0';
            else
                $GLOBALS['QuestsControl']->QuestsData->quests[$_GET['track']]->track = '1';

            if($GLOBALS['QuestsControl']->QuestsData->quests[$_GET['track']]->track == '1')
                $GLOBALS['template']->assign('tracked_quest', $GLOBALS['QuestsControl']->QuestsData->quests[$_GET['track']]);
            else
                $GLOBALS['template']->assign('tracked_quest','');

            $trackable_quests = array();
            foreach($GLOBALS['QuestsControl']->QuestsData->quests as $qid=>$quest)
            {
                if($quest->track === '1' && $qid !== $_GET['track'])
                    $GLOBALS['QuestsControl']->QuestsData->quests[$qid]->track = '0';

                if($quest->status != 3 && $quest->status != 4)
                    $trackable_quests[$qid]=$quest;
            }
            $GLOBALS['template']->assign('trackable_quests', $trackable_quests);
        }

        //no actions are being called so just show the QuestJournal's main page.
        if ($display_main_page && isset($_GET['details']) && isset($GLOBALS['QuestsControl']->QuestsData->quests[$_GET['details']]))
        {
            $quests[$_GET['details']] = QuestsControl::getActions($_GET['details'], $GLOBALS['QuestsControl']->QuestsData->quests[$_GET['details']]);
            $GLOBALS['template']->assign('statuses', QuestContainer::$statuses);
            $GLOBALS['template']->assign('quest', $GLOBALS['QuestsControl']->QuestsData->quests[$_GET['details']]);
            $GLOBALS['template']->assign('contentLoad', './templates/content/quests/QuestDetails.tpl');
        }

        else if($display_main_page)
        {
            //sending information to template
            $GLOBALS['template']->assign('village', $GLOBALS['userdata'][0]['village']);
            $GLOBALS['template']->assign('statuses', QuestContainer::$statuses);

            //check to see if the user should have ems but doesn't
            $this->checkEMS();

            $quests = $GLOBALS['QuestsControl']->QuestsData->quests;
            $active = $GLOBALS['QuestsControl']->QuestsData->active;

            //getting actions
            if(is_array($quests))
            {
                foreach($quests as $qid => $quest)
                    $quests[$qid] = QuestsControl::getActions($qid, $quest);
            }

            //sending more information to the template and calling the template
            $GLOBALS['template']->assign('quests', $quests);
            $GLOBALS['template']->assign('active', $active);
            $GLOBALS['template']->assign('questing_mode', $GLOBALS['userdata'][0]['QuestingMode']);
            $GLOBALS['template']->assign('contentLoad', './templates/content/quests/QuestJournal.tpl');
        }
    }

    public function checkEMS()
    {
        if($GLOBALS['userdata'][0]['rank_id'] >= 3)
        {
            $em_count = 0;

            if(isset($GLOBALS['QuestsControl']->QuestsData->quests))
                foreach($GLOBALS['QuestsControl']->QuestsData->quests as $quest)
                    if($quest->category == 'elemental mastery' && $quest->status !=  QuestContainer::$closed)
                        $em_count += 1;

            if( $GLOBALS['userdata'][0]['rank_id'] == 3 && $em_count < 1 )
            {
                require_once(Data::$absSvrPath.'/libs/elements/Elements.php');
                $elements = (new Elements())->getUserElements();
                $GLOBALS['Events']->acceptEvent('elements_active_primary', array('new'=>$elements[0], 'old'=>'none' ));
            }
            else if($GLOBALS['userdata'][0]['rank_id'] >= 4 && $em_count < 2)
            {
                require_once(Data::$absSvrPath.'/libs/elements/Elements.php');
                $elements = (new Elements())->getUserElements();
                $GLOBALS['Events']->acceptEvent('elements_active_primary', array('new'=>$elements[0], 'old'=>'none' ));
                $GLOBALS['Events']->acceptEvent('elements_active_secondary', array('new'=>$elements[1], 'old'=>'none' ));
            }
        }
    }
}
new QuestJournal();
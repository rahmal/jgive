<?php
// No direct access to this file
defined('_JEXEC') or die;
 /**
 * jGive component helper.
 */
class JgiveHelper
{
	/**
	 * Configure the Linkbar.
	 */
	public static function addSubmenu($vName='')
	{
		$cp='';
		$campaigns='';
		$donations='';
		$reports='';
		$payoutlayout='';
		$categories='';
		$ending_camp='';
		$queue=JFactory::getApplication()->input->get('layout');

		switch($vName)
		{
			case 'cp':
			   $cp=true;
			break;

			case 'campaigns':
			   $campaigns=true;
			break;

			case 'donations':
			   $donations=true;
			break;

			case 'ending_camp':
			   $ending_camp=true;
			break;

			case 'reports':
			   if($queue=='payouts' || $queue=='edit_payout'){ //hack by Sneha, Bug #24973
					$payoutlayout=true;
				}
			   else{
					$reports=true;
				}
			break;

			case 'categories':
				$categories=true;
			break;
		}

		if(JVERSION>=3.0)
		{
			JHtmlSidebar::addEntry(JText::_('COM_JGIVE_CP'),'index.php?option=com_jgive&view=cp',$cp);
			JHtmlSidebar::addEntry(JText::_('COM_JGIVE_CAMPAIGNS'),'index.php?option=com_jgive&view=campaigns&layout=all_list',$campaigns);

			// added categories menu
			JHtmlSidebar::addEntry(JText::_('COM_JGIVE_SUBMENU_CATEGORIES'),'index.php?option=com_categories&view=categories&extension=com_jgive', $categories);
			$document = JFactory::getDocument();
			$document->addStyleDeclaration('.icon-48-helloworld ' . '{background-image: url(../media/com_helloworld/images/tux-48x48.png);}');

			JHtmlSidebar::addEntry(JText::_('COM_JGIVE_REPORTS'),'index.php?option=com_jgive&view=reports&layout=default',$reports);
			JHtmlSidebar::addEntry(JText::_('COM_JGIVE_PAYOUT_REPORTS'),'index.php?option=com_jgive&view=reports&layout=payouts',$payoutlayout);
			JHtmlSidebar::addEntry(JText::_('COM_JGIVE_DONATIONS'),'index.php?option=com_jgive&view=donations&layout=all',$donations);
		//Added by Sneha, new menu for ending campaigns report
			JHtmlSidebar::addEntry(JText::_('COM_JGIVE_END_CAMPAIGNS'),'index.php?option=com_jgive&view=ending_camp',$ending_camp);
		}
		else
		{
			JSubMenuHelper::addEntry(JText::_('COM_JGIVE_CP'),'index.php?option=com_jgive&view=cp',$cp);
			JSubMenuHelper::addEntry(JText::_('COM_JGIVE_CAMPAIGNS'),'index.php?option=com_jgive&view=campaigns&layout=all_list',$campaigns);
			// added categories meni
			JSubMenuHelper::addEntry(JText::_('COM_JGIVE_SUBMENU_CATEGORIES'),'index.php?option=com_categories&view=categories&extension=com_jgive', $categories);
			$document = JFactory::getDocument();
			$document->addStyleDeclaration('.icon-48-helloworld ' . '{background-image: url(../media/com_helloworld/images/tux-48x48.png);}');

			JSubMenuHelper::addEntry(JText::_('COM_JGIVE_REPORTS'),'index.php?option=com_jgive&view=reports&layout=default',$reports);
			JSubMenuHelper::addEntry(JText::_('COM_JGIVE_PAYOUT_REPORTS'),'index.php?option=com_jgive&view=reports&layout=payouts',$payoutlayout);
			JSubMenuHelper::addEntry(JText::_('COM_JGIVE_DONATIONS'),'index.php?option=com_jgive&view=donations&layout=all',$donations);
			JSubMenuHelper::addEntry(JText::_('COM_JGIVE_END_CAMPAIGNS'),'index.php?option=com_jgive&view=ending_camp',$ending_camp);

		}
		//load bootsraped filter
		if(JVERSION>=3.0)
		{
			JHtml::_('bootstrap.tooltip');
			if($vName!='donations' and $queue!='paymentform')
			{
				JHtml::_('behavior.multiselect');
				JHtml::_('formbehavior.chosen', 'select');
			}
		}

	}
}

<?php
/**
 * @package	Jticketing
 * @copyright Copyright (C) 2009 -2010 Techjoomla, Tekdi Web Solutions . All rights reserved.
 * @license GNU GPLv2 <http://www.gnu.org/licenses/old-licenses/gpl-2.0.html>
 * @link     http://www.techjoomla.com
 */

// no direct access
defined('_JEXEC') or die('Restricted access');
jimport( 'joomla.application.component.view');

class jgiveViewReports extends JViewLegacy
{
	function display($tpl = null)
	{
		global $mainframe, $option;
		$mainframe=JFactory::getApplication();
		//load submenu
		$JgiveHelper=new JgiveHelper();
		$JgiveHelper->addSubmenu('reports');

		$option=JFactory::getApplication()->input->get('option');

		$campaignHelper=new campaignHelper();
		//get params
		$params=JComponentHelper::getParams('com_jgive');
		$this->currency_code=$params->get('currency');

		//default layout is default
		$layout=JFactory::getApplication()->input->get('layout','default');//die;
		$this->setLayout($layout);

		$filter_order_Dir=$mainframe->getUserStateFromRequest('com_jgive.filter_order_Dir','filter_order_Dir','desc','word');
		$filter_type=$mainframe->getUserStateFromRequest('com_jgive.filter_order','filter_order','goal_amount','string');

		if($layout=='default')
		{
			//set filter options
			$filter_campaign_options=array();
			if(JVERSION<3.0)
			$filter_campaign_options[]=JHtml::_('select.option','0',JText::_('COM_JGIVE_SELECT_CAMPAIGN'));
			//use helpr function
			$campaign_list=$campaignHelper->getAllCampaignOptions();
			if(!empty($campaign_list))
			{
				foreach($campaign_list as $key=>$campaign){
					$filter_campaign_options[]=JHtml::_('select.option',$campaign->id,$campaign->title);
				}
			}
			$this->filter_campaign_options=$filter_campaign_options;

			//get Campaigns Type fillter
			$this->campaign_type_filter_options=$campaignHelper->getCampaignTypeFilterOptions();
			$filter_campaign_type=$mainframe->getUserStateFromRequest($option.'filter_campaign_type','filter_campaign_type','','string');
			$lists['filter_campaign_type']=$filter_campaign_type;
			$this->lists=$lists;

			$filter_campaign=$mainframe->getUserStateFromRequest($option.'filter_campaign','filter_campaign','','string');
			//$filter_campaign=JString::strtolower($filter_campaign);//not needed?
			$lists['filter_campaign']=$filter_campaign;
			$this->lists=$lists;

			$cwdonations=$this->get('CampaignWiseDonations');
			$this->cwdonations=$cwdonations;

			$total=$this->get('Total');
			$this->total=$total;

			$pagination=$this->get('Pagination');
			$this->pagination=$pagination;

			// Category fillter
			$this->cat_options=$campaignHelper->getCampaignsCategories();
			//get filter value and set list
			$filter_campaign_cat=$mainframe->getUserStateFromRequest('com_jgive.filter_campaign_cat','filter_campaign_cat','','INT');
			$lists['filter_campaign_cat']=$filter_campaign_cat;
			$this->lists=$lists;

			//organization_individual_type filter since version 1.5.1
			$campaignHelper=new campaignHelper();
			$org_ind_type=$campaignHelper->organization_individual_type();
			$this->filter_org_ind_type_report= $org_ind_type;

			$filter_org_ind_type_report=$mainframe->getUserStateFromRequest('com_jgive'.'filter_org_ind_type_report','filter_org_ind_type_report');
			$lists['filter_org_ind_type_report']=$filter_org_ind_type_report;
		}

		if($layout=='payouts')
		{
			$payouts=$this->get('Payouts');
			$this->payouts=$payouts;

			$total=$this->get('Total');
			$this->total=$total;

			$pagination=$this->get('Pagination');
			$this->pagination=$pagination;
		}

		$payout_id=JFactory::getApplication()->input->get('payout_id','');
		if($layout=='edit_payout')
		{
			$getPayoutFormData=$this->get('PayoutFormData');
			$this->getPayoutFormData=$getPayoutFormData;

			$payee_options=array();
			$payee_options[]=JHtml::_('select.option','0',JText::_('Select payee'));

			if(!empty($getPayoutFormData))
			{
				foreach($getPayoutFormData as $payout){
					$payee_options[]=JHtml::_('select.option',$payout->creator_id,$payout->first_name.' '.$payout->last_name);
				}
			}

			$this->payee_options=$payee_options;

			$task=JFactory::getApplication()->input->get('task');
			$this->task=$task;
			$payout_data=array();
			if(!empty($payout_id))
			{
				$payout_data=$this->get('SinglePayoutData');
			}
			$this->assignRef('payout_data',$payout_data);
		}

		$payee_name=$mainframe->getUserStateFromRequest('com_jgive', 'payee_name','', 'string' );

		$lists['payee_name']=$payee_name;
		$lists['order_Dir']=$filter_order_Dir;
		$lists['order']=$filter_type;

		$this->lists=$lists;

		//set toolbar
		$this->_setToolBar($layout,$payout_id);
		//@since joomla 3.0 render sidebar
		if(JVERSION>=3.0)
			$this->sidebar = JHtmlSidebar::render();

		parent::display($tpl);
	}

	function _setToolBar($layout,$payout_id)
	{
		$document=JFactory::getDocument();
		$document->addStyleSheet(JUri::base().'components/com_jgive/assets/css/jgive.css');
		$bar=JToolBar::getInstance('toolbar');

		$layout=JFactory::getApplication()->input->get('layout');

		if ($layout=='payouts')
		{
			JToolBarHelper::title(JText::_('COM_JGIVE_PAYOUT_REPORTS'),'icon-48-jgive.png');
			JToolBarHelper::addNew('reports.add');
			JToolBarHelper::DeleteList(JText::_('COM_JGIVE_DELETE_PAYOUT_CONFIRM'),'deletePayouts','JTOOLBAR_DELETE');
			// CSV EXPORT
			if(JVERSION >= 3.0)
			{
				//JToolBarHelper::custom('reports.csvexportpayouts', 'icon-32-save.png', 'icon-32-save.png',JText::_("CSV_EXPORT"), false);

				$button = "<a class='btn'
				class='button'
				type='submit'
				onclick=\"javascript:document.getElementById('task').value = 'reports.csvexportpayouts';
				document.adminForm.submit();
				document.getElementById('task').value = '';\" href='#'>
				<span title='Export' class='icon-32-save'>
				</span>".JText::_('CSV_EXPORT')."</a>";
				$bar->appendButton( 'Custom', $button);
			}
			else
			{
				$button = "<a href='#' onclick=\"javascript:document.getElementById('task').value = 'reports.csvexportpayouts';document.getElementById('controller').value = 'reports';document.adminForm.submit();\" ><span class='icon-32-save' title='Export'></span>".JText::_('CSV_EXPORT')."</a>";
				$bar->appendButton( 'Custom', $button);
			}
		}
		elseif($layout=='edit_payout')
		{
			JToolBarHelper::title(JText::_('COM_JGIVE_EDIT_PAYOUT'),'icon-48-jgive.png');
			JToolBarHelper::back(JText::_('COM_JGIVE_BACK'),'index.php?option=com_jgive&view=reports&layout=payouts');

			if($layout=='edit_payout' AND (!empty($payout_id)))
			{
				JToolBarHelper::save('reports.edit');
			}else
			{
				JToolBarHelper::save('reports.save');
			}

		}else{
			JToolBarHelper::title(JText::_('COM_JGIVE_REPORTS'),'icon-48-jgive.png');

			// CSV EXPORT
			if(JVERSION >= 3.0)
			{

				//JToolBarHelper::custom('reports.csvexport', 'icon-32-save.png', 'icon-32-save.png',JText::_("CSV_EXPORT"), false);

				$button = "<a class='btn'
				class='button'
				type='submit'
				onclick=\"javascript:document.getElementById('task').value = 'reports.csvexport';
				document.adminForm.submit();
				document.getElementById('task').value = '';\" href='#'>
				<span title='Export' class='icon-32-save'>
				</span>".JText::_('CSV_EXPORT')."</a>";

				$bar->appendButton( 'Custom', $button);

			}
			else
			{
				$button = "<a href='#' onclick=\"javascript:document.getElementById('task').value = 'reports.csvexport';document.getElementById('controller').value = 'reports';document.adminForm.submit();document.getElementById('task').value = '';\" ><span class='icon-32-save' title='Export'></span>".JText::_('CSV_EXPORT')."</a>";
				$bar->appendButton( 'Custom', $button);

				//$button = "<a class='toolbar' class='button' type='submit' onclick=\"javascript:document.getElementById('task').value = 'payment_csvexport';document.adminForm.submit();document.getElementById('task').value = '';\" href='#'><span title='Export' class='icon-32-save'></span>".JText::_('CSV_EXPORT')."</a>";


			}
		}

		JToolBarHelper::preferences( 'com_jgive' );
		// @ filter since joomla3.0
		if(JVERSION>=3.0 AND $layout=='default')
		{
			JHtmlSidebar::setAction('index.php?option=com_jgive&view=reports&layout=default');

			//@Type filter
			$campaignHelper=new campaignHelper();
			$campaign_type=$campaignHelper->filedToShowOrHide('campaign_type');

			if($campaign_type)
			{
				JHtmlSidebar::addFilter(
					JText::_('COM_JGIVE_FILTER_SELECT_TYPE'),
					'filter_campaign_type',
					JHtml::_('select.options',$this->campaign_type_filter_options, 'value', 'text', $this->lists['filter_campaign_type'], true)
				);
			}
			//@ Categorty filter
			JHtmlSidebar::addFilter(
				JText::_('COM_JGIVE_CAMPAIGN_CATEGORIES'),
				'filter_campaign_cat',
				JHtml::_('select.options', $this->cat_options, 'value', 'text', $this->lists['filter_campaign_cat'], true)
			);

			//@ Organization filter
			JHtmlSidebar::addFilter(
				JText::_('COM_JGIVE_SELECT_TYPE_ORG_INDIVIDUALS'),
				'filter_org_ind_type_report',
				JHtml::_('select.options', $this->filter_org_ind_type_report, 'value', 'text', $this->lists['filter_org_ind_type_report'], true)
			);

			//@Campaign Filter
			JHtmlSidebar::addFilter(
				JText::_('COM_JGIVE_SELECT_CAMPAIGN'),
				'filter_campaign',
				JHtml::_('select.options', $this->filter_campaign_options, 'value', 'text', $this->lists['filter_campaign'], true)
			);
		}

	}
}
?>

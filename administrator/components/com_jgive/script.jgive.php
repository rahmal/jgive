<?php
/**
 *  @package AdminTools
 *  @copyright Copyright (c)2012-2013 Nicholas K. Dionysopoulos
 *  @license GNU General Public License version 3, or later
 *  @version $Id$
 *
 *  This program is free software: you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation, either version 3 of the License, or
 *  (at your option) any later version.
 *
 *  This program is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  You should have received a copy of the GNU General Public License
 *  along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

defined('_JEXEC') or die();

jimport('joomla.filesystem.folder');
jimport('joomla.filesystem.file');

class com_jgiveInstallerScript
{

	/** @var array The list of extra modules and plugins to install */
	private $installation_queue = array(
		// modules => { (folder) => { (module) => { (position), (published) } }* }*
		'modules'=>array(
			'admin'=>array(
			),
			'site'=>array(
			'mod_jgive_campaigns'=>0,
			'mod_jgive_campaigns_pin'=>0,
			'mod_jgive_donations'=>0
			)
		),
		// plugins => { (folder) => { (element) => (published) }* }*
		'plugins'=>array(
			'system'=>array(
				'jgive_api'=>1
			),
			'community'=>array(
				'jgive'=>0
			),
			'payment'=>array(
				'2checkout'=>0,
				'alphauserpoints'=>0,
				'authorizenet'=>1,
				'bycheck'=>1,
				'byorder'=>1,
				'ccavenue'=>0,
				'jomsocialpoints'=>0,
				'linkpoint'=>1,
				'paypal'=>1,
				'paypalpro'=>0,
				'payu'=>1,
				'amazon'=>0,
				'ogone'=>0,
				'paypal_adaptive_payment'=>0
			)
		),
		'libraries'=>array(
			'activity'=>1
		)
	);

	/** @var array Obsolete files and folders to remove*/
	private $removeFilesAndFolders = array(
		'files'	=> array(
			'administrator/components/com_jgive/admin.jgive.php',
			'components/com_jgive/views/campaigns/tmpl/all_list.xml',
			'components/com_jgive/views/donations/tmpl/all.xml',
			'jgive.log',
		),
		'folders' => array(
		)
	);

	/**
	 * method to run before an install/update/uninstall method
	 *
	 * @return void
	 */
	function preflight($type, $parent)
	{
		if(!defined('DS')){
		define('DS',DIRECTORY_SEPARATOR);
		}
		// $parent is the class calling this method
		// $type is the type of change (install, update or discover_install)
		// Only allow to install on Joomla! 2.5.0 or later
		//return version_compare(JVERSION, '2.5.0', 'ge');
	}

	/**
	 * Runs after install, update or discover_update
	 * @param string $type install, update or discover_update
	 * @param JInstaller $parent
	 */
	function postflight( $type, $parent )
	{
		// Install subextensions
		$status = $this->_installSubextensions($parent);

		// Remove obsolete files and folders
		$removeFilesAndFolders = $this->removeFilesAndFolders;
		$this->_removeObsoleteFilesAndFolders($removeFilesAndFolders);

		// Install FOF
		$fofStatus = $this->_installFOF($parent);

		// Install Techjoomla Straper
		$straperStatus = $this->_installStraper($parent);

		// Show the post-installation page
		$this->_renderPostInstallation($status, $fofStatus, $straperStatus, $parent);
	}

	/**
	 * Removes obsolete files and folders
	 *
	 * @param array $removeFilesAndFolders
	 */
	private function _removeObsoleteFilesAndFolders($removeFilesAndFolders)
	{
		// Remove files
		jimport('joomla.filesystem.file');
		if(!empty($removeFilesAndFolders['files'])) foreach($removeFilesAndFolders['files'] as $file) {
			$f = JPATH_ROOT.'/'.$file;
			if(!JFile::exists($f)) continue;
			JFile::delete($f);
		}

		// Remove folders
		jimport('joomla.filesystem.file');
		if(!empty($removeFilesAndFolders['folders'])) foreach($removeFilesAndFolders['folders'] as $folder) {
			$f = JPATH_ROOT.'/'.$folder;
			if(!file_exists($f)) continue;
				rmdir($f);
		}
	}
	/**
	 * Renders the post-installation message
	 */
	private function _renderPostInstallation($status, $fofStatus, $straperStatus, $parent)
	{
		?>

		<?php $rows = 1;?>

		<table class="adminlist">
			<thead>
				<tr>
					<th class="title" colspan="2">Extension</th>
					<th width="30%">Status</th>
				</tr>
			</thead>
			<tfoot>
				<tr>
					<td colspan="3"></td>
				</tr>
			</tfoot>
			<tbody>
				<tr class="row0">
					<td class="key" colspan="2">jGive component</td>
					<td><strong style="color: green">Installed</strong></td>
				</tr>
				<tr class="row1">
					<td class="key" colspan="2">
						<strong>Framework on Framework (FOF) <?php echo $fofStatus['version']?></strong> [<?php echo $fofStatus['date'] ?>]
					</td>
					<td><strong>
						<span style="color: <?php echo $fofStatus['required'] ? ($fofStatus['installed']?'green':'red') : '#660' ?>; font-weight: bold;">
							<?php echo $fofStatus['required'] ? ($fofStatus['installed'] ?'Installed':'Not Installed') : 'Already up-to-date'; ?>
						</span>
					</strong></td>
				</tr>
				<tr class="row0">
					<td class="key" colspan="2">
						<strong>TechJoomla Strapper <?php echo $straperStatus['version']?></strong> [<?php echo $straperStatus['date'] ?>]
					</td>
					<td><strong>
						<span style="color: <?php echo $straperStatus['required'] ? ($straperStatus['installed']?'green':'red') : '#660' ?>; font-weight: bold;">
							<?php echo $straperStatus['required'] ? ($straperStatus['installed'] ?'Installed':'Not Installed') : 'Already up-to-date'; ?>
						</span>
					</strong></td>
				</tr>
				<?php if (count($status->modules)) : ?>
				<tr>
					<th>Module</th>
					<th>Client</th>
					<th></th>
				</tr>
				<?php foreach ($status->modules as $module) : ?>
				<tr class="row<?php echo ($rows++ % 2); ?>">
					<td class="key"><?php echo $module['name']; ?></td>
					<td class="key"><?php echo ucfirst($module['client']); ?></td>
					<td><strong style="color: <?php echo ($module['result'])? "green" : "red"?>"><?php echo ($module['result'])?'Installed':'Not installed'; ?></strong></td>
				</tr>
				<?php endforeach;?>
				<?php endif;?>
				<?php if (count($status->plugins)) : ?>
				<tr>
					<th>Plugin</th>
					<th>Group</th>
					<th></th>
				</tr>
				<?php foreach ($status->plugins as $plugin) : ?>
				<tr class="row<?php echo ($rows++ % 2); ?>">
					<td class="key"><?php echo ucfirst($plugin['name']); ?></td>
					<td class="key"><?php echo ucfirst($plugin['group']); ?></td>
					<td><strong style="color: <?php echo ($plugin['result'])? "green" : "red"?>"><?php echo ($plugin['result'])?'Installed':'Not installed'; ?></strong></td>
				</tr>
				<?php endforeach; ?>
				<?php endif; ?>
				<?php if (count($status->libraries)) : ?>
				<tr class="row1">
					<th>Library</th>
					<th></th>
					<th></th>
					</tr>
				<?php foreach ($status->libraries as $libraries) : ?>
				<tr class="row2 <?php //echo ($rows++ % 2); ?>">
					<td class="key"><?php echo ucfirst($libraries['name']); ?></td>
					<td class="key"></td>
					<td><strong style="color: <?php echo ($libraries['result'])? "green" : "red"?>"><?php echo ($libraries['result'])?'Installed':'Not installed'; ?></strong>
					<?php
						if(!empty($libraries['result'])) // if installed then only show msg
						{
						echo $mstat=($libraries['status']? "<span class=\"label label-success\">Enabled</span>" : "<span class=\"label label-important\">Disabled</span>");

						}
					?>

					</td>
				</tr>
				<?php endforeach;?>
				<?php endif;?>


			<?php
			if (!empty($status->app_install))
			{
				if (count($status->app_install)) : ?>
				<tr class="row1">
					<th>EasySocial App</th>
					<th></th>
					<th></th>
					</tr>
				<?php foreach ($status->app_install as $app_install) : ?>
				<tr class="row2 <?php //echo ($rows++ % 2); ?>">
					<td class="key"><?php echo ucfirst($app_install['name']); ?></td>
					<td class="key"></td>
					<td><strong style="color: <?php echo ($app_install['result'])? "green" : "red"?>"><?php echo ($app_install['result'])?'Installed':'Not installed'; ?></strong>
					<?php
						if(!empty($app_install['result'])) // if installed then only show msg
						{
						echo $mstat=($app_install['status']? "<span class=\"label label-success\">Enabled</span>" : "<span class=\"label label-important\">Disabled</span>");

						}
					?>

					</td>
				</tr>
				<?php endforeach;?>
				<?php endif;
			}
			?>

			</tbody>
		</table>

		<br/>
		<div class="row-fluid">
			<div class="span12">
				<div class="alert alert-info">
					<a class="btn"
					href="index.php?option=com_jgive&task=updateAllCampaignsSuccessStatus"
					target="_blank">
						Click here
					</a>
					 to update 'Campaign Success Status' for all campaigns.
				</div>
			</div>
		</div>
		<br/>

		<?php
	}

	/**
	 * Installs subextensions (modules, plugins) bundled with the main extension
	 *
	 * @param JInstaller $parent
	 * @return JObject The subextension installation status
	 */
	private function _installSubextensions($parent)
	{
		$src = $parent->getParent()->getPath('source');

		$db = JFactory::getDbo();

		$status = new JObject();
		$status->modules = array();
		$status->plugins = array();

		// Modules installation
		if(count($this->installation_queue['modules'])) {
			foreach($this->installation_queue['modules'] as $folder => $modules) {
				if(count($modules)) foreach($modules as $module => $modulePreferences) {
					// Install the module
					if(empty($folder)) $folder = 'site';
					$path = "$src/modules/$folder/$module";
					if(!is_dir($path)) {
						$path = "$src/modules/$folder/mod_$module";
					}
					if(!is_dir($path)) {
						$path = "$src/modules/$module";
					}
					if(!is_dir($path)) {
						$path = "$src/modules/mod_$module";
					}
					if(!is_dir($path)) continue;
					// Was the module already installed?
					$sql = $db->getQuery(true)
						->select('COUNT(*)')
						->from('#__modules')
						->where($db->qn('module').' = '.$db->q('mod_'.$module));
					$db->setQuery($sql);
					$count = $db->loadResult();
					$installer = new JInstaller;
					$result = $installer->install($path);
					$status->modules[] = array(
						'name'=>'mod_'.$module,
						'client'=>$folder,
						'result'=>$result
					);
					// Modify where it's published and its published state
					if(!$count) {
						// A. Position and state
						list($modulePosition, $modulePublished) = $modulePreferences;
						if($modulePosition == 'cpanel') {
							$modulePosition = 'icon';
						}
						$sql = $db->getQuery(true)
							->update($db->qn('#__modules'))
							->set($db->qn('position').' = '.$db->q($modulePosition))
							->where($db->qn('module').' = '.$db->q('mod_'.$module));
						if($modulePublished) {
							$sql->set($db->qn('published').' = '.$db->q('1'));
						}
						$db->setQuery($sql);
						$db->execute();

						// B. Change the ordering of back-end modules to 1 + max ordering
						if($folder == 'admin') {
							$query = $db->getQuery(true);
							$query->select('MAX('.$db->qn('ordering').')')
								->from($db->qn('#__modules'))
								->where($db->qn('position').'='.$db->q($modulePosition));
							$db->setQuery($query);
							$position = $db->loadResult();
							$position++;

							$query = $db->getQuery(true);
							$query->update($db->qn('#__modules'))
								->set($db->qn('ordering').' = '.$db->q($position))
								->where($db->qn('module').' = '.$db->q('mod_'.$module));
							$db->setQuery($query);
							$db->execute();
						}

						// C. Link to all pages
						$query = $db->getQuery(true);
						$query->select('id')->from($db->qn('#__modules'))
							->where($db->qn('module').' = '.$db->q('mod_'.$module));
						$db->setQuery($query);
						$moduleid = $db->loadResult();

						$query = $db->getQuery(true);
						$query->select('*')->from($db->qn('#__modules_menu'))
							->where($db->qn('moduleid').' = '.$db->q($moduleid));
						$db->setQuery($query);
						$assignments = $db->loadObjectList();
						$isAssigned = !empty($assignments);
						if(!$isAssigned) {
							$o = (object)array(
								'moduleid'	=> $moduleid,
								'menuid'	=> 0
							);
							$db->insertObject('#__modules_menu', $o);
						}
					}
				}
			}
		}

		// Plugins installation
		if(count($this->installation_queue['plugins'])) {
			foreach($this->installation_queue['plugins'] as $folder => $plugins) {
				if(count($plugins)) foreach($plugins as $plugin => $published) {
					$path = "$src/plugins/$folder/$plugin";
					if(!is_dir($path)) {
						$path = "$src/plugins/$folder/plg_$plugin";
					}
					if(!is_dir($path)) {
						$path = "$src/plugins/$plugin";
					}
					if(!is_dir($path)) {
						$path = "$src/plugins/plg_$plugin";
					}
					if(!is_dir($path)) continue;

					// Was the plugin already installed?
					$query = $db->getQuery(true)
						->select('COUNT(*)')
						->from($db->qn('#__extensions'))
						->where($db->qn('element').' = '.$db->q($plugin))
						->where($db->qn('folder').' = '.$db->q($folder));
					$db->setQuery($query);
					$count = $db->loadResult();

					$installer = new JInstaller;
					$result = $installer->install($path);

					$status->plugins[] = array('name'=>'plg_'.$plugin,'group'=>$folder, 'result'=>$result);

					if($published && !$count) {
						$query = $db->getQuery(true)
							->update($db->qn('#__extensions'))
							->set($db->qn('enabled').' = '.$db->q('1'))
							->where($db->qn('element').' = '.$db->q($plugin))
							->where($db->qn('folder').' = '.$db->q($folder));
						$db->setQuery($query);
						$db->execute();
					}
				}
			}
		}
		if(count($this->installation_queue['libraries'])) {
			foreach($this->installation_queue['libraries']  as $folder=>$status1) {

					$path = "$src/libraries/$folder";

					$query = $db->getQuery(true)
						->select('COUNT(*)')
						->from($db->qn('#__extensions'))
						->where('( '.($db->qn('name').' = '.$db->q($folder)) .' OR '. ($db->qn('element').' = '.$db->q($folder)) .' )')
						->where($db->qn('folder').' = '.$db->q($folder));
					$db->setQuery($query);
					$count = $db->loadResult();

					$installer = new JInstaller;
					$result = $installer->install($path);

					$status->libraries[] = array('name'=>$folder,'group'=>$folder, 'result'=>$result,'status'=>$status1);
					//print"<pre>"; print_r($status->plugins); die;

					if($published && !$count) {
						$query = $db->getQuery(true)
							->update($db->qn('#__extensions'))
							->set($db->qn('enabled').' = '.$db->q('1'))
							->where('( '.($db->qn('name').' = '.$db->q($folder)) .' OR '. ($db->qn('element').' = '.$db->q($folder)) .' )')
							->where($db->qn('folder').' = '.$db->q($folder));
						$db->setQuery($query);
						$db->query();
					}
			}
		}
		//install easysocial plugin
		if(file_exists(JPATH_ADMINISTRATOR . '/components/com_easysocial/includes/foundry.php'))
		{
			require_once( JPATH_ADMINISTRATOR . '/components/com_easysocial/includes/foundry.php' );
			$installer     = Foundry::get( 'Installer' );
			// The $path here refers to your application path
			$installer->load( $src."/plugins/easysocial_camp_plg" );
			$plg_install=$installer->install();
			$status->app_install[] = array('name'=>'easysocial_camp_plg','group'=>'easysocial_camp_plg', 'result'=>$plg_install,'status'=>'1');
			//print_r($plg_install->installable);die;
		}

		return $status;
	}


	private function _installFOF($parent)
	{
		$src = $parent->getParent()->getPath('source');

		// Install the FOF framework
		jimport('joomla.filesystem.folder');
		jimport('joomla.filesystem.file');
		jimport('joomla.utilities.date');

		/*$source = $src.'/fof';*/
		//changed by manoj
		$source = $src.'/tj_lib_fof';

		if(!defined('JPATH_LIBRARIES')) {
			$target = JPATH_ROOT.'/libraries/fof';
		} else {
			$target = JPATH_LIBRARIES.'/fof';
		}
		$haveToInstallFOF = false;
		if(!file_exists($target)) {
			$haveToInstallFOF = true;
		} else {
			$fofVersion = array();
			if(JFile::exists($target.'/version.txt')) {
				$rawData = JFile::read($target.'/version.txt');
				$info = explode("\n", $rawData);
				$fofVersion['installed'] = array(
					'version'	=> trim($info[0]),
					'date'		=> new JDate(trim($info[1]))
				);
			} else {
				$fofVersion['installed'] = array(
					'version'	=> '0.0',
					'date'		=> new JDate('2011-01-01')
				);
			}
			$rawData = JFile::read($source.'/version.txt');
			$info = explode("\n", $rawData);
			$fofVersion['package'] = array(
				'version'	=> trim($info[0]),
				'date'		=> new JDate(trim($info[1]))
			);

			$haveToInstallFOF = $fofVersion['package']['date']->toUNIX() > $fofVersion['installed']['date']->toUNIX();
		}

		$installedFOF = false;
		if($haveToInstallFOF) {
			$versionSource = 'package';
			$installer = new JInstaller;
			$installedFOF = $installer->install($source);
		} else {
			$versionSource = 'installed';
		}

		if(!isset($fofVersion)) {
			$fofVersion = array();
			if(JFile::exists($target.'/version.txt')) {
				$rawData = JFile::read($target.'/version.txt');
				$info = explode("\n", $rawData);
				$fofVersion['installed'] = array(
					'version'	=> trim($info[0]),
					'date'		=> new JDate(trim($info[1]))
				);
			} else {
				$fofVersion['installed'] = array(
					'version'	=> '0.0',
					'date'		=> new JDate('2011-01-01')
				);
			}
			$rawData = JFile::read($source.'/version.txt');
			$info = explode("\n", $rawData);
			$fofVersion['package'] = array(
				'version'	=> trim($info[0]),
				'date'		=> new JDate(trim($info[1]))
			);
			$versionSource = 'installed';
		}

		if(!($fofVersion[$versionSource]['date'] instanceof JDate)) {
			$fofVersion[$versionSource]['date'] = new JDate();
		}

		return array(
			'required'	=> $haveToInstallFOF,
			'installed'	=> $installedFOF,
			'version'	=> $fofVersion[$versionSource]['version'],
			'date'		=> $fofVersion[$versionSource]['date']->format('Y-m-d'),
		);
	}

	private function _installStraper($parent)
	{
		$src = $parent->getParent()->getPath('source');

		// Install the FOF framework
		jimport('joomla.filesystem.folder');
		jimport('joomla.filesystem.file');
		jimport('joomla.utilities.date');
		$source = $src.'/tj_strapper';
		$target = JPATH_ROOT.'/media/techjoomla_strapper';

		$haveToInstallStraper = false;
		if(!file_exists($target)) {
			$haveToInstallStraper = true;
		} else {
			$straperVersion = array();
			if(JFile::exists($target.'/version.txt')) {
				$rawData = JFile::read($target.'/version.txt');
				$info = explode("\n", $rawData);
				$straperVersion['installed'] = array(
					'version'	=> trim($info[0]),
					'date'		=> new JDate(trim($info[1]))
				);
			} else {
				$straperVersion['installed'] = array(
					'version'	=> '0.0',
					'date'		=> new JDate('2011-01-01')
				);
			}
			$rawData = JFile::read($source.'/version.txt');
			$info = explode("\n", $rawData);
			$straperVersion['package'] = array(
				'version'	=> trim($info[0]),
				'date'		=> new JDate(trim($info[1]))
			);

			$haveToInstallStraper = $straperVersion['package']['date']->toUNIX() > $straperVersion['installed']['date']->toUNIX();
		}

		$installedStraper = false;
		if($haveToInstallStraper) {
			$versionSource = 'package';
			$installer = new JInstaller;
			$installedStraper = $installer->install($source);
		} else {
			$versionSource = 'installed';
		}

		if(!isset($straperVersion)) {
			$straperVersion = array();
			if(JFile::exists($target.'/version.txt')) {
				$rawData = JFile::read($target.'/version.txt');
				$info = explode("\n", $rawData);
				$straperVersion['installed'] = array(
					'version'	=> trim($info[0]),
					'date'		=> new JDate(trim($info[1]))
				);
			} else {
				$straperVersion['installed'] = array(
					'version'	=> '0.0',
					'date'		=> new JDate('2011-01-01')
				);
			}
			$rawData = JFile::read($source.'/version.txt');
			$info = explode("\n", $rawData);
			$straperVersion['package'] = array(
				'version'	=> trim($info[0]),
				'date'		=> new JDate(trim($info[1]))
			);
			$versionSource = 'installed';
		}

		if(!($straperVersion[$versionSource]['date'] instanceof JDate)) {
			$straperVersion[$versionSource]['date'] = new JDate();
		}

		return array(
			'required'	=> $haveToInstallStraper,
			'installed'	=> $installedStraper,
			'version'	=> $straperVersion[$versionSource]['version'],
			'date'		=> $straperVersion[$versionSource]['date']->format('Y-m-d'),
		);
	}

	/**
	 * method to install the component
	 *
	 * @return void
	 */
	function install($parent)
	{
		// $parent is the class calling this method
	}

	/**
	 * method to uninstall the component
	 *
	 * @return void
	 */
	function uninstall($parent)
	{
		// $parent is the class calling this method
	}

	/**
	 * method to update the component
	 *
	 * @return void
	 */
	function update($parent)
	{
		// $parent is the class calling this method

		//since version 1.0.2
		$this->fix_db_on_update();

		//create core tables
		$this->runSQL($parent,'install.sql');

		$db=JFactory::getDBO();
		$config=JFactory::getConfig();


		if(JVERSION>=3.0)
			$configdb=$config->get('db');
		else
			$configdb=$config->getValue('config.db');

		//get dbprefix
		if(JVERSION>=3.0)
			$dbprefix=$config->get('dbprefix');
		else
			$dbprefix=$config->getValue('config.dbprefix');

		//install country table(#__tj_country) if it does not exists

		$query="SELECT table_name
		FROM information_schema.tables
		WHERE table_schema='".$configdb."'
		AND table_name='".$dbprefix."tj_country'";
		$db->setQuery($query);
		$check=$db->loadResult();
		if(!$check){
			//Lets create the table
			$this->runSQL($parent,'country.sql');
		}

		//install region table(#__tj_region) if it does not exists


		$query="SELECT table_name
		FROM information_schema.tables
		WHERE table_schema='".$configdb."'
		AND table_name='".$dbprefix."tj_region'";
		$db->setQuery($query);
		$check=$db->loadResult();
		if(!$check){
			//Lets create the table
			$this->runSQL($parent,'region.sql');
		}
		//since version 1.6
		//install region table(#__tj_city) if it does not exists
		$query="SELECT table_name
		FROM information_schema.tables
		WHERE table_schema='".$configdb."'
		AND table_name='".$dbprefix."tj_city'";
		$db->setQuery($query);
		$check=$db->loadResult();
		if(!$check){
			//Lets create the table
			$this->runSQL($parent,'city.sql');
		}
	}

	function runSQL($parent,$sqlfile)
	{
		$db = JFactory::getDBO();
		// Obviously you may have to change the path and name if your installation SQL file ;)
		if(method_exists($parent, 'extension_root')) {
			$sqlfile = $parent->getPath('extension_root').DS.'backend'.DS.'sqlfiles'.DS.$sqlfile;
		} else {
			$sqlfile = $parent->getParent()->getPath('extension_root').DS.'sqlfiles'.DS.$sqlfile;
		}
		// Don't modify below this line
		$buffer = file_get_contents($sqlfile);
		if ($buffer !== false) {
			jimport('joomla.installer.helper');
			$queries = JInstallerHelper::splitSql($buffer);
			if (count($queries) != 0) {
				foreach ($queries as $query)
				{
					$query = trim($query);
					if ($query != '' && $query{0} != '#') {
						$db->setQuery($query);
						if (!$db->execute()) {
							JError::raiseWarning(1, JText::sprintf('JLIB_INSTALLER_ERROR_SQL_ERROR', $db->stderr(true)));
							return false;
						}
					}
				}
			}
		}
	}//end run sql

	//since version 1.0.2
	function fix_db_on_update()
	{
		$db = JFactory::getDBO();
		//since version 1.0.2
		//check if column - type exists
		$query="SHOW COLUMNS FROM #__jg_campaigns WHERE `Field` = 'type' AND `Type` = 'VARCHAR(50)'";
		$db->setQuery($query);
		$check=$db->loadResult();
		if(!$check)
		{
			$query="ALTER TABLE  `#__jg_campaigns` ADD  `type` VARCHAR( 50 ) NOT NULL DEFAULT 'donation' AFTER  `modified`";
			$db->setQuery($query);
			//$db->loadResult();
			if ( !$db->execute() ) {
				JError::raiseError( 500, $db->stderr() );
			}
		}

		//since version 1.0.2
		//check if column - max_donors exists
		$query="SHOW COLUMNS FROM #__jg_campaigns WHERE `Field` = 'max_donors' AND `Type` = 'INT(11)'";
		$db->setQuery($query);
		$check=$db->loadResult();
		if(!$check)
		{
			$query="ALTER TABLE  `#__jg_campaigns` ADD  `max_donors` INT( 11 ) NOT NULL DEFAULT '0' AFTER  `type`";
			$db->setQuery($query);
			//$db->loadResult();
			if ( !$db->execute() ) {
				JError::raiseError( 500, $db->stderr() );
			}
		}


		//since version 1.0.3
		//check if column - type exists
		$query="SHOW COLUMNS FROM #__jg_campaigns WHERE `Field` = 'minimum_amount' AND `Type` = 'INT(11)'";
		$db->setQuery($query);
		$check=$db->loadResult();
		if(!$check)
		{
			$query="ALTER TABLE  `#__jg_campaigns` ADD  `minimum_amount` INT( 11 ) NOT NULL DEFAULT  '0' COMMENT  'minimum amount for transaction' AFTER  `max_donors`";
			$db->setQuery($query);
			//$db->loadResult();
			if ( !$db->execute() ) {
				JError::raiseError( 500, $db->stderr() );
			}
		}

		//since version 1.0.3
		//check if column - order_id exists
		$query="SHOW COLUMNS FROM #__jg_orders WHERE `Field` = 'order_id' AND `Type` = 'VARCHAR(23)'";
		$db->setQuery($query);
		$check=$db->loadResult();
		if(!$check)
		{
			$query="ALTER TABLE `#__jg_orders` ADD `order_id` VARCHAR( 23 ) NOT NULL AFTER `id`";
			$db->setQuery($query);
			//$db->loadResult();
			if ( !$db->execute() ) {
				JError::raiseError( 500, $db->stderr() );
			}
		}

		//since version 1.0.3
		//check if column - order_id exists
		$query="SHOW COLUMNS FROM #__jg_orders WHERE `Field` = 'fund_holder' AND `Type` = 'TINYINT(1)'";
		$db->setQuery($query);
		$check=$db->loadResult();
		if(!$check)
		{
			$query="ALTER TABLE `#__jg_orders` ADD `fund_holder` TINYINT( 1 ) NOT NULL DEFAULT '0' COMMENT 'To whose account money was originally transferred to: 0-admin, 1-campaign promoter' AFTER `donor_id`";
			$db->setQuery($query);
			//$db->loadResult();
			if ( !$db->execute() ) {
				JError::raiseError( 500, $db->stderr() );
			}
		}
		//since version 1.5
		//check if column - featured exists
		$query="SHOW COLUMNS FROM #__jg_campaigns WHERE `Field` = 'featured' AND `Type` = 'TINYINT(3)'";
		$db->setQuery($query);
		$check=$db->loadResult();
		if(!$check)
		{
			$query="ALTER TABLE `#__jg_campaigns` ADD `featured` TINYINT( 3 ) NOT NULL default '0' COMMENT 'Set if campaign is Marks as featured'";
			$db->setQuery($query);
			//$db->loadResult();
			if ( !$db->execute() ) {
				JError::raiseError( 500, $db->stderr() );
			}
		}
		//since version 1.5
		//check if column - group name exists
		$query="SHOW COLUMNS FROM #__jg_campaigns WHERE `Field` = 'group_name' AND `Type` = 'varchar(250)' ";
		$db->setQuery($query);
		$check=$db->loadResult();
		if(!$check)
		{
			$query="ALTER TABLE `#__jg_campaigns` ADD `group_name` varchar(250) NOT NULL AFTER  `phone` ";
			$db->setQuery($query);
			//$db->loadResult();
			if ( !$db->execute() ) {
				JError::raiseError( 500, $db->stderr() );
			}
		}
		//since version 1.5
		//check if column - website_address exists
		$query="SHOW COLUMNS FROM #__jg_campaigns WHERE `Field` = 'website_address' AND `Type` = 'varchar(250)'";
		$db->setQuery($query);
		$check=$db->loadResult();
		if(!$check)
		{
			$query="ALTER TABLE `#__jg_campaigns` ADD `website_address` varchar(250) NOT NULL AFTER  `group_name` ";
			$db->setQuery($query);
			//$db->loadResult();
			if ( !$db->execute() ) {
				JError::raiseError( 500, $db->stderr() );
			}
		}
		//since version 1.5.1
		//check if column - category_id exists
		$query="SHOW COLUMNS FROM #__jg_campaigns WHERE `Field` = 'category_id' AND `Type` = 'int(11)'";
		$db->setQuery($query);
		$check=$db->loadResult();
		if(!$check)
		{
			$query="ALTER TABLE `#__jg_campaigns` ADD `category_id` int(11) NOT NULL AFTER  `type` ";
			$db->setQuery($query);
			//$db->loadResult();
			if ( !$db->execute() ) {
				JError::raiseError( 500, $db->stderr() );
			}
		}
		//since version 1.5.1
		//check if column - organization or individual type exists
		$query="SHOW COLUMNS FROM #__jg_campaigns WHERE `Field` = 'org_ind_type' AND `Type` = 'varchar(250)'";
		$db->setQuery($query);
		$check=$db->loadResult();
		if(!$check)
		{
			$query="ALTER TABLE `#__jg_campaigns` ADD `org_ind_type` varchar(250) NOT NULL AFTER  `category_id` ";
			$db->setQuery($query);
			//$db->loadResult();
			if ( !$db->execute() ) {
				JError::raiseError( 500, $db->stderr() );
			}
		}

		//since version 1.6
		//check if column - recurring_count exists
		$query="SHOW COLUMNS FROM #__jg_donations WHERE `Field` = 'recurring_count' AND `Type` = 'int(11)'";
		$db->setQuery($query);
		$check=$db->loadResult();
		if(!$check)
		{
			$query="ALTER TABLE `#__jg_donations` ADD `recurring_count` int(11) NOT NULL AFTER  `recurring_frequency` ";
			$db->setQuery($query);
			//$db->loadResult();
			if ( !$db->execute() ) {
				JError::raiseError( 500, $db->stderr() );
			}
		}


		//since version 1.6
		//check if column - subscr_id type exists
		$query="SHOW COLUMNS FROM #__jg_donations WHERE `Field` = 'subscr_id' AND `Type` = 'varchar(100)'";
		$db->setQuery($query);
		$check=$db->loadResult();
		if(!$check)
		{
			$query="ALTER TABLE `#__jg_donations` ADD `subscr_id` varchar(100) NOT NULL AFTER  `recurring_frequency` ";
			$db->setQuery($query);
			//$db->loadResult();
			if ( !$db->execute() ) {
				JError::raiseError( 500, $db->stderr() );
			}
		}

		//since version 1.6
		//check if column - donation_id type exists
		$query="SHOW COLUMNS FROM #__jg_orders WHERE `Field` = 'donation_id' AND `Type` = 'int(11)'";
		$db->setQuery($query);
		$check=$db->loadResult();
		if(!$check)
		{
			$query="ALTER TABLE `#__jg_orders` ADD `donation_id` int(11) NOT NULL AFTER  `donor_id` ";
			$db->setQuery($query);
			//$db->loadResult();
			if ( !$db->execute() ) {
				JError::raiseError( 500, $db->stderr() );
			}
		}

		//since version 1.6
		//check if column - vat_number type exists
		$query="SHOW COLUMNS FROM #__jg_orders WHERE `Field` = 'vat_number' AND `Type` = 'varchar(100)'";
		$db->setQuery($query);
		$check=$db->loadResult();
		if(!$check)
		{
			$query="ALTER TABLE `#__jg_orders` ADD `vat_number` varchar(100) NOT NULL AFTER  `fee` ";
			$db->setQuery($query);
			//$db->loadResult();
			if ( !$db->execute() ) {
				JError::raiseError( 500, $db->stderr() );
			}
		}

		//since version 1.6
		//check if column - vat_number type exists
		$query="ALTER TABLE `#__jg_donations` MODIFY `recurring_frequency` varchar(100)";
		$db->setQuery($query);
		//$db->loadResult();
		if ( !$db->execute() ) {
			JError::raiseError( 500, $db->stderr() );
		}

		//since version 1.6
		//check if column - video_provider type exists
		$query="SHOW COLUMNS FROM #__jg_campaigns_images WHERE `Field` = 'video_provider' AND `Type` = 'varchar(50)'";
		$db->setQuery($query);
		$check=$db->loadResult();
		if(!$check)
		{
			$query="ALTER TABLE `#__jg_campaigns_images` ADD `video_provider` varchar(50) NOT NULL AFTER  `path` ";
			$db->setQuery($query);
			//$db->loadResult();
			if ( !$db->execute() ) {
				JError::raiseError( 500, $db->stderr() );
			}
		}

		//since version 1.6
		//check if column - video_url type exists
		$query="SHOW COLUMNS FROM #__jg_campaigns_images WHERE `Field` = 'video_url' AND `Type` = 'text'";
		$db->setQuery($query);
		$check=$db->loadResult();
		if(!$check)
		{
			$query="ALTER TABLE `#__jg_campaigns_images` ADD `video_url` text NOT NULL AFTER  `video_provider` ";
			$db->setQuery($query);
			//$db->loadResult();
			if ( !$db->execute() ) {
				JError::raiseError( 500, $db->stderr() );
			}
		}

		//since version 1.6
		//check if column - video_url type exists
		$query="SHOW COLUMNS FROM #__jg_campaigns_images WHERE `Field` = 'video_img' AND `Type` = 'tinyint(1)'";
		$db->setQuery($query);
		$check=$db->loadResult();
		if(!$check)
		{
			$query="ALTER TABLE `#__jg_campaigns_images` ADD `video_img` tinyint(1) NOT NULL AFTER  `video_url` ";
			$db->setQuery($query);
			//$db->loadResult();
			if ( !$db->execute() ) {
				JError::raiseError( 500, $db->stderr() );
			}
		}
		//gallery_image
		//since version 1.6
		//check if column - video_url type exists
		$query="SHOW COLUMNS FROM #__jg_campaigns_images WHERE `Field` = 'gallery_image' AND `Type` = 'tinyint(1)'";
		$db->setQuery($query);
		$check=$db->loadResult();
		if(!$check)
		{
			$query="ALTER TABLE `#__jg_campaigns_images` ADD `gallery_image` tinyint(1) NOT NULL AFTER  `video_img` ";
			$db->setQuery($query);
			//$db->loadResult();
			if ( !$db->execute() ) {
				JError::raiseError( 500, $db->stderr() );
			}
		}

		//other_city
		//since version 1.6
		//check if column - other_city type exists
		$query="SHOW COLUMNS FROM #__jg_campaigns WHERE `Field` = 'other_city' AND `Type` = 'tinyint(1)'";
		$db->setQuery($query);
		$check=$db->loadResult();
		if(!$check)
		{
			$query="ALTER TABLE `#__jg_campaigns` ADD `other_city` tinyint(1) NOT NULL AFTER  `city` ";
			$db->setQuery($query);
			//$db->loadResult();
			if ( !$db->execute() ) {
				JError::raiseError( 500, $db->stderr() );
			}
		}
		//change transaction_id column size
		$query="SHOW COLUMNS FROM #__jg_payouts WHERE `Field` = 'transaction_id' AND `Type` = 'varchar(15)'";
		$db->setQuery($query);
		$check=$db->loadResult();
		if($check)
		{
			$query="ALTER TABLE `#__jg_payouts` MODIFY `transaction_id` varchar(50) NOT NULL";
			$db->setQuery($query);
			//$db->loadResult();
			if ( !$db->execute() ) {
				JError::raiseError( 500, $db->stderr() );
			}
		}

		//js_groupid
		//since version 1.6
		//check if column - js_groupid type exists
		$query="SHOW COLUMNS FROM #__jg_campaigns WHERE `Field` = 'js_groupid' AND `Type` = 'int(11)'";
		$db->setQuery($query);
		$check=$db->loadResult();
		if(!$check)
		{
			$query="ALTER TABLE `#__jg_campaigns` ADD `js_groupid` int(11) NOT NULL AFTER `featured` ";
			$db->setQuery($query);
			//$db->loadResult();
			if ( !$db->execute() ) {
				JError::raiseError( 500, $db->stderr() );
			}
		}

		//js_groupid
		//since version 1.6
		//check if column - comment is exists
		$query="SHOW COLUMNS FROM #__jg_donations WHERE `Field` = 'comment' AND `Type` = 'text'";
		$db->setQuery($query);
		$check=$db->loadResult();
		if(!$check)
		{
			$query="ALTER TABLE `#__jg_donations` ADD `comment` text NOT NULL AFTER `recurring_count` ";
			$db->setQuery($query);
			//$db->loadResult();
			if ( !$db->execute() ) {
				JError::raiseError( 500, $db->stderr() );
			}
		}

		//js_groupid
		//since version 1.6
		//check if column - comment is exists
		$query="SHOW COLUMNS FROM #__jg_donations WHERE `Field` = 'comment' AND `Type` = 'text'";
		$db->setQuery($query);
		$check=$db->loadResult();
		if(!$check)
		{
			$query="ALTER TABLE `#__jg_donations` ADD `comment` text NOT NULL AFTER `recurring_count` ";
			$db->setQuery($query);
			//$db->loadResult();
			if ( !$db->execute() ) {
				JError::raiseError( 500, $db->stderr() );
			}
		}

		//added by sagar for custom project
		$query="SHOW COLUMNS FROM `#__jg_donations` WHERE `Field` = 'giveback_id' AND `Type` = 'INT(11)'";
		$db->setQuery($query);
		$check=$db->loadResult();
		if(!$check)
		{
			$query="ALTER TABLE  `#__jg_donations` ADD   `giveback_id` int(11) NOT NULL COMMENT 'id of jg_campaigns_givebacks' AFTER  `order_id`";
			$db->setQuery($query);
			if ( !$db->execute() ) {
				JError::raiseError( 500, $db->stderr() );
			}
		}

		$query="SHOW COLUMNS FROM `#__jg_campaigns_givebacks` WHERE `Field` = 'quantity' AND `Type` = 'INT(11)'";
		$db->setQuery($query);
		$check=$db->loadResult();
		if(!$check)
		{
			$query="ALTER TABLE  `#__jg_campaigns_givebacks` ADD   `quantity` int(11) NOT NULL COMMENT 'quantity of giveback' AFTER  `order`";
			$db->setQuery($query);
			if ( !$db->execute() ) {
				JError::raiseError( 500, $db->stderr() );
			}
		}

		$query="SHOW COLUMNS FROM `#__jg_campaigns_givebacks` WHERE `Field` = 'total_quantity' AND `Type` = 'INT(11)'";
		$db->setQuery($query);
		$check=$db->loadResult();
		if(!$check)
		{
			$query="ALTER TABLE  `#__jg_campaigns_givebacks` ADD   `total_quantity` int(11) NOT NULL COMMENT 'total quantity of giveback' AFTER  `quantity`";
			$db->setQuery($query);
			//$db->loadResult();
			if ( !$db->execute() ) {
				JError::raiseError( 500, $db->stderr() );
			}
		}

		$query="SHOW COLUMNS FROM `#__jg_campaigns_givebacks` WHERE `Field` = 'image_path' AND `Type` = 'varchar(400)'";
		$db->setQuery($query);
		$check=$db->loadResult();
		if(!$check)
		{
			$query="ALTER TABLE  `#__jg_campaigns_givebacks` ADD   `image_path` varchar(400) NOT NULL COMMENT 'image_path of giveback' AFTER  `total_quantity`";
			$db->setQuery($query);
			//$db->loadResult();
			if ( !$db->execute() ) {
				JError::raiseError( 500, $db->stderr() );
			}
		}

		//success_status
		//since version 1.6.3
		//check if column - success_status type exists
		$query="SHOW COLUMNS FROM #__jg_campaigns WHERE `Field` = 'success_status' AND `Type` = 'int(1)'";
		$db->setQuery($query);
		$check=$db->loadResult();
		if(!$check)
		{
			$query="ALTER TABLE `#__jg_campaigns` ADD `success_status` int(1) NOT NULL DEFAULT '0' COMMENT '0 - Ongoing, 1 - Successful, -1 - Failed' AFTER `js_groupid` ";
			$db->setQuery($query);
			//$db->loadResult();
			if ( !$db->execute() ) {
				JError::raiseError( 500, $db->stderr() );
			}
		}

		//processed_flag
		//since version 1.6.3
		//check if column - processed_flag type exists
		$query="SHOW COLUMNS FROM #__jg_campaigns WHERE `Field` = 'processed_flag' AND `Type` = 'varchar(50)'";
		$db->setQuery($query);
		$check=$db->loadResult();
		if(!$check)
		{
			$query="ALTER TABLE `#__jg_campaigns` ADD `processed_flag` varchar(50) DEFAULT 'NA' COMMENT 'NA - NA, SP - Success Processed, RF - Refunded' AFTER `success_status` ";
			$db->setQuery($query);
			//$db->loadResult();
			if ( !$db->execute() ) {
				JError::raiseError( 500, $db->stderr() );
			}
		}

	}

}//end class
?>

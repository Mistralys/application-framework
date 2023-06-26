<?php
/**
 * File containing the {@link Application_Updaters} class.
 *
 * @package Application
 * @subpackage Maintenance
 */

declare(strict_types=1);

use AppUtils\ClassHelper;
use AppUtils\Interface_Stringable;use AppUtils\OutputBuffering;
use AppUtils\OutputBuffering_Exception;

/**
 * UI generator for the available maintenance scripts. Displays the
 * script details and dispatches actions to the selected scripts ("updaters").
 *
 * @package Application
 * @subpackage Maintenance
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 */
class Application_Updaters
{
    public const REQUEST_PARAM_UPDATER_ID = 'updater_id';

    /**
    * @var Application_Updaters_Updater[]
    */
	protected array $updaters = array();
	protected string $classesFolder;
	protected Application_Driver $driver;
	protected Application_Request $request;
	protected UI $ui;
	protected UI_Themes_Theme $theme;

	public function __construct()
	{
		$this->driver = Application_Driver::getInstance();

		if(!$this->driver->getUser()->isDeveloper())
		{
		    $this->driver->redirectWithInfoMessage(
		        '<b>'.t('Note:').'</b> '.t('The maintenance tools are reserved for developers.'),
		        APP_URL
	        );
		}

		$this->classesFolder = $this->driver->getClassesFolder().'/Updaters';
		$this->request = $this->driver->getRequest();
		$this->ui = $this->driver->getUI();
		$this->theme = $this->ui->getTheme();

		$this->loadUpdaters();

		$this->request->registerParam(self::REQUEST_PARAM_UPDATER_ID)->setEnum(array_keys($this->updaters));
	}

	protected function loadUpdaters() : void
	{
		if(!file_exists($this->classesFolder) || !is_dir($this->classesFolder)) {
			return;
		}

		$d = new DirectoryIterator($this->classesFolder);
		foreach($d as $item) {
			if(!$item->isFile()) {
				continue;
			}

			$ext = strtolower(pathinfo($item->getFilename(), PATHINFO_EXTENSION));
			if($ext != 'php') {
				continue;
			}

			$id = pathinfo($item->getFilename(), PATHINFO_FILENAME);
			if($id=='Updater') {
				continue;
			}

			$this->updaters[$id] = $this->createUpdater($id);
		}
	}

	protected ?Application_Updaters_Updater $activeUpdater = null;

	public function start() : void
	{
	    $updaterID = $this->request->getParam(self::REQUEST_PARAM_UPDATER_ID);

	    if(!empty($updaterID))
        {
	        $this->activeUpdater = $this->getByID($updaterID);
	        echo $this->activeUpdater->start();
	        return;
	    }

	    $this->showSelectionScreen();
	}

	protected function createUpdater(string $id) : Application_Updaters_Updater
	{
		$class = ClassHelper::requireResolvedClass(APP_CLASS_NAME.'_Updaters_'.$id);

        return ClassHelper::requireObjectInstanceOf(
            Application_Updaters_Updater::class,
            new $class($this)
        );
	}

	public function getByID($id)
	{
		return $this->updaters[$id];
	}

   /**
    * @param string[] $exclude Optional list of version numbers to exclude
    * @return Application_Updaters_Updater[]
    */
	public function getAll($exclude=array())
	{
		if(empty($exclude)) {
			return $this->updaters;
		}

		$result = array();
		foreach($this->updaters as $updater) {
			$valid = true;
			foreach($exclude as $version) {
				if($updater->hasSpecificVersion($version)) {
					$valid = false;
					break;
				}
			}

			if($valid) {
				$result[] = $updater;
			}
		}

		usort($result, array($this, 'handle_sortUpdaters'));

		return $result;
	}

   /**
    * Retrieves all updater scripts for the specified version, if any.
    *
    * @param string $version
    * @return Application_Updaters_Updater[]
    */
	public function getForVersion($version)
	{
		$result = array();
		foreach($this->updaters as $updater) {
			if($updater->hasSpecificVersion($version)) {
				$result[] = $updater;
			}
		}

		usort($result, array($this, 'handle_sortUpdaters'));

		return $result;
	}

	public function updaterExists($id)
	{
		return isset($this->updaters[$id]);
	}

	public function showSelectionScreen()
	{
		$html = $this->ui->getPage()->renderTemplate(
		    'updaters.selection-screen',
		    array(
		        'instance' => $this
		    )
	    );

		echo $this->renderPage(t('%1$s maintenance', $this->driver->getAppNameShort()), $html);
	}

	public function handle_sortUpdaters($a, $b)
	{
	    return strnatcasecmp($a->getListLabel(), $b->getListLabel());
	}

	public function isEnabled(Application_Updaters_Interface $updater) : bool
	{
		$versions = $updater->getValidVersions();
		if($versions === '*') {
			return true;
		}

		if(!is_array($versions)) {
		    $versions = array($versions);
		}

		return in_array($this->driver->getVersion(), $versions, true);
	}

    /**
     * @param string|number|UI_Renderable_Interface$title
     * @param string|number|UI_Renderable_Interface $content
     * @return string
     * @throws Application_Exception
     * @throws OutputBuffering_Exception
     */
	public function renderPage($title, $content) : string
	{
		return
            $this->renderPageHeader($title).
		    toString($content).
		    $this->renderPageFooter();
	}

    /**
     * @param string|number|UI_Renderable_Interface $title
     * @return string
     * @throws Application_Exception|OutputBuffering_Exception
     */
	public function renderPageHeader($title) : string
	{
	    $this->ui->addStylesheet('ui-updaters.css');

        OutputBuffering::start();

		?><!DOCTYPE html>
		<html lang="en">
			<head>
				<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
				<title><?php echo toString($title) ?></title>
				<link rel="shortcut icon" href="favicon.ico"/>
				<?php
				    echo $this->ui->renderHeadIncludes();
				?>
			</head>
			<body>
				 <div class="container">
				 	<br/>
				 	<div class="navbar">
						<div class="navbar-inner">
							<span class="pull-right instance-label">
                                <?php pts('Instance:'); echo strtoupper(APP_INSTANCE_ID) ?>
                                |
                                <?php pts('Version:'); echo $this->driver->getExtendedVersion() ?>
                            </span>
							<a class="brand" href="<?php echo $this->buildURL() ?>"><?php pt('%1$s maintenance', $this->driver->getAppNameShort()) ?></a>
							<ul class="nav">
								<li><a href="xml/monitor"><?php pt('Health Monitor') ?></a></li>
								<li><a href="./"><?php pt('Back to %1$s', $this->driver->getAppNameShort()) ?></a></li>
							</ul>
						</div>
					</div>
		          <?php

		if(isset($this->activeUpdater))
        {
		    ?>
		    <ul class="breadcrumb">
		        <li>
                    <a href="<?php echo $this->buildURL() ?>"><?php pt('Dashboard') ?></a>
                    <span class="divider">/</span>
                </li>
		        <li class="active">
                    <?php echo $this->activeUpdater->getLabel() ?>
                </li>
		    </ul>
             <?php
		}

        return OutputBuffering::get();
	}

    /**
     * @param array<string,string|int|float|bool|Interface_Stringable|NULL> $params
     * @return string
     */
    public function buildURL(array $params=array()) : string
	{
        return Application_Request::getInstance()
            ->buildURL($params, Application_Bootstrap_Screen_Updaters::DISPATCHER_NAME);
	}

	public function renderPageFooter() : string
	{
        OutputBuffering::start();

		?>
					</div>
					<p><br></p>
				</body>
			</html>
		<?php

        return OutputBuffering::get();
	}
}

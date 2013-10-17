<?php

namespace mako\reactor\tasks;

use \mako\utility\Str;

/**
 * App task.
 *
 * @author     Frederic G. Østby
 * @copyright  (c) 2008-2013 Frederic G. Østby
 * @license    http://www.makoframework.com/license
 */

class App extends \mako\reactor\Task
{
	//---------------------------------------------
	// Class properties
	//---------------------------------------------

	/**
	 * Task information.
	 * 
	 * @var array
	 */

	protected static $taskInfo = array
	(
		'up' => array
		(
			'description' => 'Takes the application online.',
		),
		'down' => array
		(
			'description' => 'Takes the application offline.',
		),
		'generate_secret' => array
		(
			'description' => 'Generates a new application secret.',
		),
	);

	//---------------------------------------------
	// Class constructor, destructor etc ...
	//---------------------------------------------

	// Nothing here

	//---------------------------------------------
	// Class methods
	//---------------------------------------------

	/**
	 * Returns path to the lockfile.
	 * 
	 * @access  protected
	 * @return  string
	 */

	protected function lockFile()
	{
		return MAKO_APPLICATION_PATH . '/storage/offline';
	}

	/**
	 * Takes the application online.
	 * 
	 * @access  public
	 */

	public function up()
	{
		if(file_exists($this->lockFile()))
		{
			if(!is_writable($this->lockFile()))
			{
				return $this->output->error('Unable to delete the lock file. Make sure that your "app/storage" directory is writable.');
			}

			unlink($this->lockFile());
		}

		$this->output->writeln('Your application is now <green>online</green>.');
	}

	/**
	 * Takes the application offline.
	 * 
	 * @access  public
	 */

	public function down()
	{
		if(!is_writable(MAKO_APPLICATION_PATH . '/storage'))
		{
			return $this->output->error('Unable to create the lock file. Make sure that your "app/storage" directory is writable.');
		}

		touch($this->lockFile());

		$this->output->writeln('Your application is now <red>offline</red>.');
	}

	/**
	 * Generates a new application secret.
	 * 
	 * @access  public
	 */

	public function generate_secret()
	{
		$configFile = MAKO_APPLICATION_PATH . '/config/application.php';

		if(!is_writable($configFile))
		{
			return $this->output->error('Unable to generate a new secret. Make sure that the "app/config/application.php" file is writable.');
		}

		$secret = str_replace(array('"', '\''), array('|', '/'), Str::random(Str::ALNUM . Str::SYMBOLS, 32));

		$contents = file_get_contents($configFile);

		$contents = preg_replace('/\'secret\'(\s*)=>(\s*)\'(.*)\',/', '\'secret\'$1=>$2\'' . $secret . '\',', $contents);

		file_put_contents($configFile, $contents);

		$this->output->writeln('A new secret has been generated.');
	}
}

/** -------------------- End of file -------------------- **/
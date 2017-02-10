<?php

namespace Ivory;

use Ivory\Fbi;

/**
 * PHP class for storing and interracting with OMX Player on Rapsberry Pi
 */
class Omxplayer {

	/**
	 * An array of flags and options chosen to use during executing
	 * @var array
	 */
	protected $options;

	/**
	 * The file to play
	 * @var string
	 */
	protected $file;

	/**
	 * The length of the file in seconds. 
	 * @var int
	 */
	protected $length;

	/**
	 * An instance of FBI to make sure the background is black
	 * @var Ivory\Fbi
	 */
	protected $background;

	public function __construct()
	{
		/**
		 * The Fbi will be used to display a black background PNG file. This 
		 * allows for a blackbox affect when the video is not the same resolution
		 * as the screen. If this is not done, then whatever was on the screen
		 * prior (command, terminal output, etc..) will be visible in the 
		 * background where the video doesn't match the resolution. 
		 * @var Fbi
		 */
		$this->imageBackground = (new Fbi)
			->image( realpath(__FILE__) . '/assets/Black.png')
			->displayFor(0) // Display it until we are done with it
			->withAutoZoom();
	}

	/**
	 * A common standard set of settings that most people will want to start with
	 * @return $this
	 */
	public function standard()
	{
		$this->noOsd()->blackBackground();

		return $this;
	}

	/**
	 * Set the Audio Out to be the native audio out for the device
	 * @return $this
	 */
	public function hdmiAudioOut()
	{
		$this->selectedOptions['o'] = 'hdmi';

		return $this;
	}

	public function in3D()
	{
		$this->selectedOptions['3'] = '';

		return $this;
	}

	/**
	 * Say where to start the video in hh:mm:ss format
	 * @param  string $timestamp 
	 * @return $this
	 */
	public function startAt(string $timestamp)
	{
		$this->selectedOptions['l'] =  $timestamp;

		return $this;
	}

	/**
	 * Turn off On Screen Display of information
	 * @return return
	 */
	public function noOsd()
	{
		$this->selectedOptions['no-osd'] = '';

		return $this;
	}

	public function loop()
	{
		$this->selectedOptions['loop'] = '';

		return $this;
	}

	public function blackBackground()
	{
		$this->selectedOptions['blank'] = '';

		return $this;
	}

	/**
	 * Start the player using the selected options for the file provided
	 * @param  string $file Absoulte path to File
	 * @return        
	 */
	public function play()
	{
		$this->imageBackground->display();
		system('omxplayer ' . $this->_compileOptions() . ' ' . $this->file . ' > /dev/null 2>&1');
		//sleep($this->length);
		$this->imageBackground->terminate();
		$this->terminate();
	}

	/**
	 * Sets the Video to use and the length the video plays for
	 * @param  string  $file   The File to play
	 * @param  int $length How many seconds long is the video
	 * @return void
	 */
	public function video($file, $length = false)
	{
		$this->file = $file;
		$this->length = $length ? $length : $this->videoLengthInSeconds();
	}

	protected function _compileOptions()
	{
		$compiled = '';
		foreach( $this->options as $option => $value ) :
			if( strlen($option) > 1 ) $compiled.= "--{$option} {$value}";
			else $compiled.= "-{$option} {$value}";
		endforeach;

		return $compiled;
	}

	/**
	 * Return the length of the video from the metadata
	 * @return [type] [description]
	 */
	public function videoLength()
	{
		$command = system('omxplayer -i ' . $this->file . ' /home/pi/omx.info 2>&1 || cat /home/pi/omx.info | grep "Duration:"');
		$segments = explode(",", $command);
		return trim(substr($segments[0], 10));
	}

	/**
	 * Return the video length in seconds so the script knows how long to sleep for before moving on. 
	 * @return [type] [description]
	 */
	public function videoLengthInSeconds()
	{
		$length = $this->videoLength();
		return strtotime($length) - strtotime('TODAY');
	}
	
	/**
	 * Terminates all running instances of OMX Player
	 * @return 
	 */
	public function terminate()
	{
		system('sudo killall omxplayer.bin > /dev/null 2>&1');
	}
}


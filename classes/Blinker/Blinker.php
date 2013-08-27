<?php defined('SYSPATH') OR die('No direct script access.');

/**
 * Makes flag in session and set how log flag is setted.
 * 
 * @author t
 *
 */
class Blinker_Blinker{
	/**
	 * Time duration for notification blinks
	 *
	 * 60*2 = 120 : 2 minutes
	 * debug: 20s
	 */
	const DURATION	= 120;
	
	/**
	 * Session key
	 *
	 * @var string
	 */
	protected static $KEY = 'NotificatiorKEY';
	
	/**
	 * Set given value in session 
	 * 
	 * @param string $key
	 * @param mixed $value
	 * @return Blinker_Blinker
	 */
	public function __set($key, $value)
	{
		$session = Session::instance()->get(self::$KEY);
		$session[$key] = $value;
		Session::instance()->set(self::$KEY, $session);
		return $this;
	}
	
	/**
	 * Get parameter stored in session
	 * 
	 * @param unknown $key
	 * @return mixed
	 */
	public function __get($key)
	{
		$session = Session::instance()->get(self::$KEY);
		return $session[$key];
	}
	/**
	 * Checks if user already saw the message but maybe have ommited it
	 * @return boolean
	 */
	public function already_shown ($last_login)
	{
		$status = self::get_status();
	
		/**
		 * If first blinking session was show then return TRUE
		 * 
		 * when login_status+tolerance is betweem 'from' and 'to' status it means status has
		 * the state from first blink, after this we have to check if time is greater than end
		 * limes if its possitive it means user already saw the blink.
		 */		
		$tolerance = self::DURATION ;
		if (
				($last_login + $tolerance) 	>= $status['from'] AND 
				($last_login + $tolerance)	<= $status['to']
		)
		{
			$now = time();
			if ($now > $status['to'])
			{
				return TRUE;
			}
			return FALSE;
		}
		else
		{
			return TRUE;
		}
	}

	/**
	 * Force set blink status to FALSE
	 */
	public static function stop_blink ()
	{
		self::update_status(FALSE);
	}
	
	/**
	 * Return session as array
	 * @return mixed
	 */
	public static function get_status ()
	{
		$session = Session::instance()->get(self::$KEY);
		$session['from'] = isset($session['from']) ? $session['from'] : NULL;
		$session['to'] = isset($session['to']) ? $session['to'] : NULL;
		$session['active'] = isset($session['active']) ? $session['active'] : FALSE;
		return $session;
	}
	
	/**
	 * Sets frag. If no parameters given time is setted from current time to currnet time + DURATION 
	 *
	 * @param int $from_time
	 * @param int $to_time
	 */
	public static function start_blink ($from_time = NULL, $to_time = NULL)
	{
		if ($from_time === NULL)
		{
			$from_time = time();
		}
		if ($to_time === NULL)
		{
			$to_time = $from_time + self::DURATION;
		}
	
	
		Session::instance()->set(self::$KEY,
		array(
			'active' 	=> TRUE,
			'from' 		=> $from_time,
			'to' 		=> $to_time
		)
		);
	
	}
	
	/**
	 * Force sets active to given value
	 *
	 * @param boolean $active
	 */
	protected static function update_status ($active)
	{
		$session = Session::instance()->get(self::$KEY);
		$session['active'] = $active;
		Session::instance()->set(self::$KEY, $session);
	}
	
	/**
	 * check blinking status, return current status
	 */
	public static function check_blink ()
	{
		$session = Session::instance()->get(self::$KEY);
		$now = time();
		$active = isset($session['active']) ? $session['active'] : FALSE;
		$to_time = isset($session['to']) ? $session['to'] : NULL;
		$from_time = isset($session['from']) ? $session['from'] : NULL;
		if ($to_time < $now)
		{
			$active = FALSE;
			self::update_status($active);
		}
	
		return array(
				'active' 	=> $active,
				'from'		=> $from_time,
				'to' 		=> $to_time
		);
	}
	/**
	 * Deletes session
	 */
	public static function clear()
	{
		Session::instance()->delete(self::$KEY);
	}
}

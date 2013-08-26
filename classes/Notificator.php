<?php defined('SYSPATH') OR die('No direct script access.');

class Notificator extends Blinker{	
	/**
	 * Validity time for new notification
	 * 60*60*24*14 //secounds * minuts * hours * days
	 * 
	 * @var int
	 */
	protected $validity = 1209600;
	
	protected $user;
	
	protected $team;

	public function __construct($user, $team)
	{
		$this->user = $user;
		$this->team = $team;
	}
	
	public function run ()
	{
		
	}
	/**
	 * Check unread messages
	 * 
	 * @return boolean
	 */
	public function is_user_unread_messages ()
	{
		
		//If unread messages then set this flag
		$set_notification = FALSE;
		$user = $this->user;
		
		if ( ! $user) return FALSE;
	
	//	$last_login = $user->last_login;
	//	$status = $this->get_status();

		$unread = $this->user_unread_query();//$last_login, $status);
		$unread->find();

		return $unread->loaded();	
	}
	/**
	 * Get unread messages
	 * 
	 * @return boolean|unknown
	 */
	public function get_user_unread_messages ()
	{
		$user = $this->user;
		
		if ( ! $user) return FALSE;
		
//		$last_login = $user->last_login;
//		$status = $this->get_status();
		
		$unread = $this->user_unread_query();//$last_login, $status);
		return $unread;
	}
	/**
	 * set unread messages as read
	 */
	public function set_user_read_messages ()
	{
		$unreads = $this->get_user_unread_messages()->find_all();
	
		foreach ($unreads as $unread)
		{
			$unread->read_recipient = date('Y-m-d H:i:s', time());
			$unread->update();
		}
	}
	/**
	 * user query
	 * 
	 * @param int $last_login
	 * @param Blinker $status
	 * @return ORM
	 */
	private function user_unread_query ()//$last_login, $status)
	{
		$user = $this->user;
		
		if ($this->already_shown($user->last_login))
		{
			$status = $this->get_status();
			
			$unread = $user->request
				->where('read_recipient', '=', NULL)
				->where('date', '>=', date('Y-m-d H:i:s', $status['to']));
		}
		else
		{
			$unread = $user->request
				->where('read_recipient', '=', NULL)
				->where('date', '>', date('Y-m-d H:i:s', $user->last_login - $this->validity) );
		}
		/*
		$user = $this->user;
		
		if ($this->already_shown($last_login))
		{
			$unread = $user->request
				->where('read_recipient', '=', NULL)
				->where('date', '>=', date('Y-m-d H:i:s', $status['to']));	
		}
		else
		{
			$unread = $user->request
				->where('read_recipient', '=', NULL)
				->where('date', '>', date('Y-m-d H:i:s', $last_login - $this->validity) );
		}
		
		*/
		return $unread;
	}
	public function is_team_unread_messages ()
	{
		$set_notification = FALSE;
		$team = $this->team;
		if ( ! $team) return FALSE;
		
		return FALSE;
	}
	public function get_team_unread_messages ()
	{
		
	}
	public function team_unread_query ()
	{
		$user = $this->user;
		$team = $this->team;
		
		if ($this->already_shown($last_login))
		
		return $unread;
	}
	public function set_team_read_messages ()
	{
		
	}
	
	
}

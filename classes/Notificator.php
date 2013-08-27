<?php defined('SYSPATH') OR die('No direct script access.');

/**
 * Implements requests and ORM query. Notificator takes the decision if new
 * messages are shown. Class has two fields to read dependiencies of request
 * from there.
 *
 * @author t
 *
 */

class Notificator extends Blinker{	
	
	/**
	 * Validity time for new notification, it's limit to not take the old messages
	 * 60*60*24*14 //secounds * minuts * hours * days = 2 weeks
	 * 
	 * @var int
	 */
	protected $validity = 1209600;
	
	/**
	 * User field has requests
	 * @var ORM
	 */
	protected $user;
	
	/**
	 * Team fields has erquests
	 * @var ORM
	 */
	protected $team;

	public function __construct($user, $team)
	{
		$this->user = $user;
		$this->team = $team;
	}

	/**
	 * Check if field is correct, so it can contain needed informations about requests.
	 * 
	 * @param string, object $field
	 * @return boolean
	 */
	public function is_valid($field)
	{
		//field as object
		$check = $field;
		//field is not object it's just a name of object
		if (is_string($field))
		{
			//check if field has object
			if (property_exists($this, $field))
			{
				$check = $this->$field;
			}
			else
			{
				//class not exist so check is NULL
				$check = NULL;
			}
		}
		return ($check === NULL) ? FALSE : TRUE;
	}
	//-----------USER MESSAGES
	/**
	 * Check unread messages
	 * 
	 * @return boolean
	 */
	public function is_user_unread_messages ()
	{
		$user = $this->user;
		
		if ( ! $user) throw new UnexpectedValueException('User can\'t be NULL');

		$unread = $this->user_unread_query();
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
		
		if ( ! $user) throw new UnexpectedValueException('User can\'t be NULL');
		
		$unread = $this->user_unread_query();
		
		return $unread;
	}
	/**
	 * set unread messages as read
	 */
	public function set_user_read_messages ()
	{
		$unreads = $this->user_all_unread_query()->find_all();
	
		foreach ($unreads as $unread)
		{
			$unread->read_recipient = date('Y-m-d H:i:s', time());
			$unread->update();
		}
	}
	/**
	 * user query show requests depeds if user just logged in or new messages are delivered
	 * 
	 * @param int $last_login
	 * @param Blinker $status
	 * @return ORM
	 */
	private function user_unread_query ()
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
			$unread = $this->user_all_unread_query();
		}

		return $unread;
	}
	private function user_all_unread_query ()
	{
		$user = $this->user;
		$unread = $user->request
			->where('read_recipient', '=', NULL)
			->where('date', '>', date('Y-m-d H:i:s', $user->last_login - $this->validity) );
		return $unread;
	}
	//-----------TEAM MESSAGES
	/**
	 * 
	 * 
	 * @return boolean
	 */
	public function is_team_unread_messages ()
	{
		$team = $this->team;
		
		if ( ! $team) throw new UnexpectedValueException('Team have to be valid');
		
		$unread = $this->team_unread_query();
		$unread->find();
		
		return $unread->loaded();
	}
	public function get_team_unread_messages ()
	{
		$team = $this->team;

		if ($this->is_valid($team) === FALSE) throw new UnexpectedValueException('Team have to be valid');
		
		$unread = $this->team_unread_query();
		
		return $unread;
	}
	private function team_unread_query ()
	{
		$team = $this->team;
		$user = $this->user;
		
		if ($this->already_shown($user->last_login))
		{
			$status = $this->get_status();
			
			$unread = $team->request
				->where('date', '>=', date('Y-m-d H:i:s', $status['to']))
				->where('active', '=', TRUE)
				->where('status', '=', NULL)
			;
		}
		else
		{
			$unread = $this->user_all_unread_query();
		}
		
		return $unread;
	}
	private function team_all_unread_query ()
	{
		$team = $this->team;
		$user = $this->user;	
		
		$unread = $team->request
			->where('date', '>', date('Y-m-d H:i:s', $user->last_login - $this->validity) )
			->where('active', '=', TRUE)
			->where('status', '=', NULL)
		;
		
		return $unread;
	}
	public function set_team_read_messages ()
	{
		$unreads = $this->team_all_unread_query()->find_all();
		
		foreach ($unreads as $unread)
		{
			$unread->active = TRUE;
			$unread->status = FALSE;
			$unread->update();
		}
	}
	
	
}

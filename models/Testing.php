<?php

class Testing extends Model
{
	public static $mysql_table	= "testing";
	
	protected $_inline_data = ["subjects_9", "subjects_11"];
	
	public function __construct($array)
	{
		parent::__construct($array);
		
		if (!$this->isNewRecord) {
			if ($this->cabinet) {
				$this->Cabinet = Cabinet::findById($this->cabinet);
			}
		}		
	}

}
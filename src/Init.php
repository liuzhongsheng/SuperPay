<?php
namespace SuperPay;
class Init
{
	protected $baseParam  = null;
	public function __construct($param=[])
	{
		$this->baseParam = $param;
	}

    // 提交支持：commit,notify
    public function query($param,$event='commit')
    {
        $className = 'SuperPay\\'.$param['class_type_name'].'\\' . $param['class_name'];
        unset($param['class_type_name'],$param['class_name']);
        $obj       = new $className($this->baseParam);
        return $obj->$event($param);
    }
}